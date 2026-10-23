<?php

namespace App\Services;

use App\Exceptions\DigitalCardException;
use App\Models\DigitalCard;
use App\Models\DigitalCardEvent;
use App\Models\SavingInstalment;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\DigitalCardReceivedNotification;
use App\Support\DigitalCardSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Digital card lifecycle: generate (online-wallet debit) → share with a
 * downline member → recipient applies it to their next saving instalment
 * (reserved) → admin confirms (used) or rejects (released back to recipient).
 *
 * Every state change runs inside a DB transaction with row locks and writes
 * an append-only DigitalCardEvent. All ownership/status checks happen here,
 * on the server, against the database record.
 */
class DigitalCardService
{
    public const PAYMENT_METHOD = 'digital_card';

    public function __construct(private SavingAccountService $savingAccountService) {}

    // =========================================================================
    // Generation
    // =========================================================================

    public function generate(User $user, float $amount): DigitalCard
    {
        $amount = round($amount, 2);
        $min    = DigitalCardSettings::minAmount();
        $max    = DigitalCardSettings::maxAmount();

        if (!DigitalCardSettings::enabled()) {
            throw new DigitalCardException('Digital card generation is currently disabled.', 'disabled');
        }
        if ($amount < $min || $amount > $max) {
            throw new DigitalCardException(
                'Card amount must be between $' . number_format($min, 2) . ' and $' . number_format($max, 2) . '.',
                'invalid_amount'
            );
        }

        $card = DB::transaction(function () use ($user, $amount) {
            // Serialise all card wallet operations for this user so two
            // concurrent requests cannot both pass the balance check.
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->blocked) {
                throw new DigitalCardException('Your wallet is not available for card generation.', 'wallet_blocked');
            }
            if ((float) $locked->negative_pv > 0) {
                throw new DigitalCardException('Clear your negative points before generating a card.', 'negative_points');
            }

            $balance = $this->onlineBalance($locked->id);
            if ($balance + 0.00001 < $amount) {
                throw new DigitalCardException(
                    'Insufficient online wallet balance. Available: $' . number_format($balance, 2) . '.',
                    'insufficient_balance'
                );
            }

            $cardNumber = $this->newCardNumber();

            // Single negative row — same debit convention as member transfers.
            $wallet = Wallet::create([
                'user_id'          => $locked->id,
                'wallet_type'      => 'online',
                'balance'          => -$amount,
                'transaction_type' => 'debit',
                'direct_balance'   => 0.00,
                'indirect_balance' => 0.00,
                'level'            => '-',
                'commission_type'  => 'Digital Card',
                'wallet_src'       => 'digital_card',
                'description'      => 'Digital card generated (•••• ' . substr($cardNumber, -4) . ')',
                'total_earning'    => 0,
            ]);

            $card = DigitalCard::create([
                'card_number'          => $cardNumber,
                'pin'                  => $this->newPin(),
                'amount'               => $amount,
                'status'               => DigitalCard::STATUS_AVAILABLE,
                'generated_by'         => $locked->id,
                'generation_wallet_id' => $wallet->id,
                'expires_at'           => now()->addDays(DigitalCardSettings::expiryDays()),
            ]);

            TransactionLog::create([
                'user_id'          => $locked->id,
                'from_wallet_type' => 'online',
                'to_wallet_type'   => 'digital_card',
                'charge'           => 0,
                'amount'           => $amount,
                'final_amount'     => $amount,
                'description'      => "Deducted \${$amount} to generate digital card •••• " . substr($cardNumber, -4),
                'status'           => 'debit',
            ]);

            $this->logEvent($card, DigitalCardEvent::GENERATED, [
                'actor_id'    => $locked->id,
                'wallet_id'   => $wallet->id,
                'amount'      => $amount,
                'status_to'   => DigitalCard::STATUS_AVAILABLE,
                'description' => "Generated from online wallet. \${$amount} deducted (wallet entry #{$wallet->id}).",
            ]);

            return $card;
        });

        Log::info("Digital card generated: card={$card->id} user={$user->id} amount={$amount}");

        return $card;
    }

    // =========================================================================
    // Sharing
    // =========================================================================

    /**
     * Downline members (any depth, standard or saving tree) who are on a
     * saving instalment plan and can therefore use a card.
     */
    public function eligibleRecipientsQuery(User $sender): Builder
    {
        // The sender may also assign a card to themselves (to pay their own
        // instalment), as long as they are on a saving instalment plan.
        return User::query()
            ->where(fn ($q) => $q
                ->whereIn('id', DB::table('referral_trees')
                    ->select('descendant_id')
                    ->where('ancestor_id', $sender->id)
                    ->where('level', '>=', 1))
                ->orWhere('id', $sender->id))
            ->where('blocked', false)
            ->whereHas('savingInstalments');
    }

    public function isEligibleRecipient(User $sender, User $recipient): bool
    {
        return $this->eligibleRecipientsQuery($sender)->whereKey($recipient->id)->exists();
    }

    public function share(DigitalCard $card, User $sender, User $recipient, string $method): DigitalCard
    {
        if (!in_array($method, [DigitalCard::SHARE_PORTAL, DigitalCard::SHARE_EMAIL], true)) {
            throw new DigitalCardException('Invalid sharing method.', 'invalid_method');
        }

        $card = DB::transaction(function () use ($card, $sender, $recipient, $method) {
            $card = DigitalCard::whereKey($card->id)->lockForUpdate()->firstOrFail();

            if ((int) $card->generated_by !== (int) $sender->id) {
                throw new DigitalCardException('You can only share cards you generated.', 'not_owner');
            }
            if ($card->status !== DigitalCard::STATUS_AVAILABLE) {
                throw new DigitalCardException(
                    'This card has already been ' . strtolower($card->statusLabel()) . ' and cannot be shared again.',
                    'not_shareable'
                );
            }
            if ($card->isPastExpiry()) {
                throw new DigitalCardException('This card has expired.', 'expired');
            }
            if (!$this->isEligibleRecipient($sender, $recipient)) {
                throw new DigitalCardException(
                    'The selected member must be yourself or one of your downline members, and on a saving instalment plan.',
                    'ineligible_recipient'
                );
            }

            $this->transition($card, DigitalCard::STATUS_SHARED, [
                'recipient_id' => $recipient->id,
                'shared_by'    => $sender->id,
                'shared_at'    => now(),
                'share_method' => $method,
            ]);

            $this->logEvent($card, DigitalCardEvent::SHARED, [
                'actor_id'     => $sender->id,
                'from_user_id' => $sender->id,
                'to_user_id'   => $recipient->id,
                'amount'       => $card->amount,
                'status_from'  => DigitalCard::STATUS_AVAILABLE,
                'status_to'    => DigitalCard::STATUS_SHARED,
                'share_method' => $method,
                'description'  => "Shared with {$recipient->name} (@{$recipient->username}) via {$method}.",
            ]);

            return $card;
        });

        $this->notifyRecipient($card, $sender, $recipient, $method);

        return $card;
    }

    private function notifyRecipient(DigitalCard $card, User $sender, User $recipient, string $method): void
    {
        // Assigned to themselves — no "you received a card" alert or email needed.
        if ((int) $sender->id === (int) $recipient->id) {
            return;
        }

        try {
            $recipient->notify(new DigitalCardReceivedNotification($card, $sender, $method === DigitalCard::SHARE_EMAIL));

            if ($method === DigitalCard::SHARE_EMAIL) {
                $this->logEvent($card, DigitalCardEvent::EMAIL_SENT, [
                    'actor_id'    => $sender->id,
                    'to_user_id'  => $recipient->id,
                    'description' => "Card notification emailed to the recipient's registered address.",
                    'meta'        => ['delivered' => true],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("Digital card {$card->id}: recipient notification failed — " . $e->getMessage());

            if ($method === DigitalCard::SHARE_EMAIL) {
                $this->logEvent($card, DigitalCardEvent::EMAIL_SENT, [
                    'actor_id'    => $sender->id,
                    'to_user_id'  => $recipient->id,
                    'description' => 'Email delivery failed. The card is still available in the recipient\'s portal.',
                    'meta'        => ['delivered' => false],
                ]);
            }
        }
    }

    // =========================================================================
    // Using a card to pay an instalment (recipient)
    // =========================================================================

    /**
     * Recipient applies a card to their next pending saving instalment.
     * The instalment moves to 'submitted' and the card to 'reserved'; the
     * existing admin confirm / reject flow then finalises it.
     *
     * Any rejection is written to the card's history as a failed attempt.
     */
    public function payInstalment(User $user, string $cardReference, string $pin): SavingInstalment
    {
        $cardId = DigitalCard::where('reference', $cardReference)->value('id');

        try {
            return DB::transaction(function () use ($user, $cardId, $pin) {
                $card = $cardId ? DigitalCard::whereKey($cardId)->lockForUpdate()->first() : null;

                if (!$card) {
                    throw new DigitalCardException('Card not found.', 'not_found');
                }
                // Ownership first, so nothing about another member's card is revealed.
                if ((int) $card->recipient_id !== (int) $user->id) {
                    throw new DigitalCardException('This card is not assigned to you and cannot be used from your account.', 'not_recipient');
                }
                if ($this->recentFailedPinAttempts($card, $user) >= DigitalCardSettings::maxFailedPinAttempts()) {
                    throw new DigitalCardException('Too many incorrect PIN attempts. Please try again later.', 'pin_locked');
                }
                if (!hash_equals((string) $card->pin, trim($pin))) {
                    throw new DigitalCardException('Incorrect card PIN.', 'wrong_pin');
                }
                if ($card->status !== DigitalCard::STATUS_SHARED) {
                    throw new DigitalCardException(
                        'This card cannot be used — its status is ' . $card->statusLabel() . '.',
                        'not_usable'
                    );
                }
                if ($card->isPastExpiry()) {
                    throw new DigitalCardException('This card has expired.', 'expired');
                }
                if (!$this->userHasSavingPlan($user)) {
                    throw new DigitalCardException('Card payments are only available for Savings Program members.', 'not_saving_member');
                }

                $next = $this->savingAccountService->nextDueInstalment($user);
                $instalment = $next ? SavingInstalment::whereKey($next->id)->lockForUpdate()->first() : null;

                if (!$instalment || $instalment->status !== 'pending') {
                    throw new DigitalCardException('You have no pending instalment to pay.', 'no_instalment');
                }

                $required = $this->savingAccountService->requiredInstalmentTotal($instalment);
                if (round((float) $card->amount, 2) !== round($required, 2)) {
                    throw new DigitalCardException(
                        'Card amount $' . number_format((float) $card->amount, 2) .
                        ' does not match the exact amount required for instalment #' . $instalment->instalment_number .
                        ' ($' . number_format($required, 2) . ').',
                        'amount_mismatch'
                    );
                }

                $instalment->update([
                    'status'           => 'submitted',
                    'transaction_id'   => 'CARD-' . $card->card_number,
                    'payment_method'   => self::PAYMENT_METHOD,
                    'submitted_amount' => $card->amount,
                    'submitted_at'     => now(),
                    'notes'            => 'Paid with digital card •••• ' . substr($card->card_number, -4) .
                                          ' generated by user #' . $card->generated_by,
                ]);

                $this->transition($card, DigitalCard::STATUS_RESERVED, [
                    'saving_instalment_id' => $instalment->id,
                    'reserved_at'          => now(),
                ]);

                $this->logEvent($card, DigitalCardEvent::PAYMENT_SUBMITTED, [
                    'actor_id'             => $user->id,
                    'from_user_id'         => $user->id,
                    'saving_instalment_id' => $instalment->id,
                    'amount'               => $card->amount,
                    'status_from'          => DigitalCard::STATUS_SHARED,
                    'status_to'            => DigitalCard::STATUS_RESERVED,
                    'description'          => "Applied to saving instalment #{$instalment->instalment_number}. Awaiting admin confirmation.",
                ]);

                return $instalment;
            });
        } catch (DigitalCardException $e) {
            // Written after rollback so the failed attempt is kept.
            $this->logEvent($cardId, DigitalCardEvent::VERIFICATION_FAILED, [
                'actor_id'    => $user->id,
                'description' => $e->getMessage(),
                'meta'        => ['reason' => $e->reason, 'reference' => $cardId ? null : $cardReference],
            ]);
            throw $e;
        }
    }

    private function recentFailedPinAttempts(DigitalCard $card, User $user): int
    {
        return $this->countedWrongPinAttempts($card, $user->id)->count();
    }

    /**
     * Wrong-PIN attempts that still count toward the lock: inside the lock
     * window and after the most recent admin reset for this card.
     */
    private function countedWrongPinAttempts(DigitalCard $card, int $userId)
    {
        $lastResetId = DigitalCardEvent::where('digital_card_id', $card->id)
            ->where('event', DigitalCardEvent::PIN_LOCK_RESET)
            ->max('id') ?? 0;

        return DigitalCardEvent::where('digital_card_id', $card->id)
            ->where('actor_id', $userId)
            ->where('event', DigitalCardEvent::VERIFICATION_FAILED)
            ->where('meta->reason', 'wrong_pin')
            ->where('id', '>', $lastResetId)
            ->where('created_at', '>=', now()->subMinutes(DigitalCardSettings::pinLockMinutes()));
    }

    /**
     * Current PIN-lock state of a card for its recipient, or null if not locked.
     *
     * @return array{user_id:int, attempts:int, unlocks_at:\Carbon\Carbon}|null
     */
    public function pinLockStatus(DigitalCard $card): ?array
    {
        if (!$card->recipient_id) {
            return null;
        }

        $max      = DigitalCardSettings::maxFailedPinAttempts();
        $attempts = $this->countedWrongPinAttempts($card, (int) $card->recipient_id)
            ->orderByDesc('id')
            ->pluck('created_at');

        if ($attempts->count() < $max) {
            return null;
        }

        // The lock lifts when the oldest of the latest $max attempts leaves the window.
        $oldestCounted = \Carbon\Carbon::parse($attempts[$max - 1]);

        return [
            'user_id'    => (int) $card->recipient_id,
            'attempts'   => $attempts->count(),
            'unlocks_at' => $oldestCounted->addMinutes(DigitalCardSettings::pinLockMinutes()),
        ];
    }

    /** Cards whose recipient is currently PIN-locked. */
    public function pinLockedCards()
    {
        $max = DigitalCardSettings::maxFailedPinAttempts();

        $candidateIds = DigitalCardEvent::where('event', DigitalCardEvent::VERIFICATION_FAILED)
            ->where('meta->reason', 'wrong_pin')
            ->whereNotNull('digital_card_id')
            ->where('created_at', '>=', now()->subMinutes(DigitalCardSettings::pinLockMinutes()))
            ->groupBy('digital_card_id')
            ->havingRaw('COUNT(*) >= ?', [$max])
            ->pluck('digital_card_id');

        return DigitalCard::with(['recipient:id,name,username', 'generator:id,name,username'])
            ->whereIn('id', $candidateIds)
            ->get()
            ->map(fn (DigitalCard $card) => ($lock = $this->pinLockStatus($card)) ? ['card' => $card, 'lock' => $lock] : null)
            ->filter()
            ->values();
    }

    /** Admin clears the wrong-PIN lock on a card (history is kept; a reset event is added). */
    public function resetPinLock(DigitalCard $card, User $admin, ?string $reason = null): void
    {
        $lock = $this->pinLockStatus($card);

        $this->logEvent($card, DigitalCardEvent::PIN_LOCK_RESET, [
            'actor_id'    => $admin->id,
            'to_user_id'  => $card->recipient_id,
            'description' => 'PIN lock reset by admin' . ($reason ? ": {$reason}" : '') .
                             ($lock ? " ({$lock['attempts']} wrong attempt(s) cleared)." : ' (card was not locked).'),
            'meta'        => ['was_locked' => (bool) $lock, 'attempts' => $lock['attempts'] ?? 0],
        ]);
    }

    // =========================================================================
    // Hooks called from the existing instalment admin flow
    // =========================================================================

    /**
     * Called inside SavingAccountService::confirmAndDeposit()'s transaction.
     * Marks the reserved card as used. If the instalment was submitted with a
     * digital card but no valid reserved card exists, the confirmation is
     * aborted so no deposit happens without a backing card.
     */
    public function markUsedForInstalment(SavingInstalment $instalment, ?int $adminId): void
    {
        $card = DigitalCard::where('saving_instalment_id', $instalment->id)
            ->lockForUpdate()
            ->first();

        if (!$card) {
            if ($instalment->payment_method === self::PAYMENT_METHOD) {
                throw new \RuntimeException('Instalment was submitted with a digital card, but no reserved card is linked to it.');
            }
            return;
        }

        if ($card->status !== DigitalCard::STATUS_RESERVED) {
            throw new \RuntimeException("Linked digital card is '{$card->status}', expected 'reserved'.");
        }

        $this->transition($card, DigitalCard::STATUS_USED, ['used_at' => now()]);

        $this->logEvent($card, DigitalCardEvent::PAYMENT_CONFIRMED, [
            'actor_id'             => $adminId,
            'saving_instalment_id' => $instalment->id,
            'amount'               => $card->amount,
            'status_from'          => DigitalCard::STATUS_RESERVED,
            'status_to'            => DigitalCard::STATUS_USED,
            'description'          => "Instalment #{$instalment->instalment_number} confirmed. Card marked as used.",
        ]);
    }

    /** Admin rejected the instalment submission: give the card back to the recipient. */
    public function releaseForInstalment(SavingInstalment $instalment, ?int $adminId, ?string $reason = null): void
    {
        $card = DigitalCard::where('saving_instalment_id', $instalment->id)
            ->where('status', DigitalCard::STATUS_RESERVED)
            ->lockForUpdate()
            ->first();

        if (!$card) {
            return;
        }

        $this->transition($card, DigitalCard::STATUS_SHARED, [
            'saving_instalment_id' => null,
            'reserved_at'          => null,
        ]);

        $this->logEvent($card, DigitalCardEvent::PAYMENT_REJECTED, [
            'actor_id'             => $adminId,
            'saving_instalment_id' => $instalment->id,
            'status_from'          => DigitalCard::STATUS_RESERVED,
            'status_to'            => DigitalCard::STATUS_SHARED,
            'description'          => "Instalment #{$instalment->instalment_number} payment rejected" .
                                      ($reason ? ": {$reason}" : '') . '. Card returned to recipient.',
        ]);
    }

    /** Admin reversed a confirmed instalment: the card's value is restored to the recipient. */
    public function restoreAfterReversal(SavingInstalment $instalment, ?int $adminId, ?string $reason = null): void
    {
        $card = DigitalCard::where('saving_instalment_id', $instalment->id)
            ->where('status', DigitalCard::STATUS_USED)
            ->lockForUpdate()
            ->first();

        if (!$card) {
            return;
        }

        $this->transition($card, DigitalCard::STATUS_SHARED, [
            'saving_instalment_id' => null,
            'reserved_at'          => null,
            'used_at'              => null,
        ]);

        $this->logEvent($card, DigitalCardEvent::PAYMENT_REVERSED, [
            'actor_id'             => $adminId,
            'saving_instalment_id' => $instalment->id,
            'status_from'          => DigitalCard::STATUS_USED,
            'status_to'            => DigitalCard::STATUS_SHARED,
            'description'          => "Instalment #{$instalment->instalment_number} reversed" .
                                      ($reason ? ": {$reason}" : '') . '. Card is usable again by the recipient.',
        ]);
    }

    // =========================================================================
    // Cancellation, expiry and refunds
    // =========================================================================

    /** Generator cancels a card they have not shared yet. */
    public function cancelByGenerator(DigitalCard $card, User $user): DigitalCard
    {
        return DB::transaction(function () use ($card, $user) {
            $card = DigitalCard::whereKey($card->id)->lockForUpdate()->firstOrFail();

            if ((int) $card->generated_by !== (int) $user->id) {
                throw new DigitalCardException('You can only cancel cards you generated.', 'not_owner');
            }
            if ($card->status !== DigitalCard::STATUS_AVAILABLE) {
                throw new DigitalCardException('Only cards that have not been shared can be cancelled.', 'not_cancellable');
            }

            return $this->cancelAndRefund($card, $user->id, 'Cancelled by generator');
        });
    }

    /** Admin cancels an unused card (available or shared). */
    public function adminCancel(DigitalCard $card, User $admin, string $reason): DigitalCard
    {
        return DB::transaction(function () use ($card, $admin, $reason) {
            $card = DigitalCard::whereKey($card->id)->lockForUpdate()->firstOrFail();

            if (!in_array($card->status, [DigitalCard::STATUS_AVAILABLE, DigitalCard::STATUS_SHARED], true)) {
                throw new DigitalCardException(
                    "A card that is {$card->statusLabel()} cannot be cancelled.",
                    'not_cancellable'
                );
            }

            return $this->cancelAndRefund($card, $admin->id, "Cancelled by admin: {$reason}", $reason);
        });
    }

    private function cancelAndRefund(DigitalCard $card, int $actorId, string $description, ?string $reason = null): DigitalCard
    {
        $from = $card->status;

        $this->transition($card, DigitalCard::STATUS_CANCELLED, [
            'cancelled_at'  => now(),
            'cancelled_by'  => $actorId,
            'cancel_reason' => $reason ?? $description,
        ]);

        $this->logEvent($card, DigitalCardEvent::CANCELLED, [
            'actor_id'    => $actorId,
            'status_from' => $from,
            'status_to'   => DigitalCard::STATUS_CANCELLED,
            'description' => $description,
        ]);

        $this->refundToGenerator($card, $actorId, 'cancelled');

        return $card;
    }

    /**
     * Expire every unused card past its expiry date and refund the generator.
     * Reserved cards (awaiting admin review) are never expired.
     */
    public function expireDueCards(): int
    {
        $ids = DigitalCard::whereIn('status', [DigitalCard::STATUS_AVAILABLE, DigitalCard::STATUS_SHARED])
            ->where('expires_at', '<=', now())
            ->pluck('id');

        $count = 0;
        foreach ($ids as $id) {
            try {
                $done = DB::transaction(function () use ($id) {
                    $card = DigitalCard::whereKey($id)->lockForUpdate()->first();
                    if (!$card
                        || !in_array($card->status, [DigitalCard::STATUS_AVAILABLE, DigitalCard::STATUS_SHARED], true)
                        || !$card->isPastExpiry()) {
                        return false;
                    }

                    $from = $card->status;
                    $this->transition($card, DigitalCard::STATUS_EXPIRED, ['expired_at' => now()]);
                    $this->logEvent($card, DigitalCardEvent::EXPIRED, [
                        'status_from' => $from,
                        'status_to'   => DigitalCard::STATUS_EXPIRED,
                        'description' => 'Card reached its expiry date unused.',
                    ]);
                    $this->refundToGenerator($card, null, 'expired');

                    return true;
                });
                $count += $done ? 1 : 0;
            } catch (\Throwable $e) {
                Log::error("Digital card {$id} expiry failed: " . $e->getMessage());
            }
        }

        return $count;
    }

    private function refundToGenerator(DigitalCard $card, ?int $actorId, string $why): void
    {
        $amount = (float) $card->amount;
        $last4  = substr($card->card_number, -4);

        $wallet = Wallet::create([
            'user_id'          => $card->generated_by,
            'wallet_type'      => 'online',
            'balance'          => $amount,
            'transaction_type' => 'credit',
            'direct_balance'   => 0.00,
            'indirect_balance' => 0.00,
            'level'            => '-',
            'commission_type'  => 'Digital Card Refund',
            'wallet_src'       => 'digital_card',
            'description'      => "Refund for {$why} digital card •••• {$last4}",
            'total_earning'    => 0, // a refund is not an earning
        ]);

        $card->update(['refund_wallet_id' => $wallet->id]);

        TransactionLog::create([
            'user_id'          => $card->generated_by,
            'from_wallet_type' => 'digital_card',
            'to_wallet_type'   => 'online',
            'charge'           => 0,
            'amount'           => $amount,
            'final_amount'     => $amount,
            'description'      => "Refunded \${$amount} for {$why} digital card •••• {$last4}",
            'status'           => 'credit',
        ]);

        $this->logEvent($card, DigitalCardEvent::REFUNDED, [
            'actor_id'    => $actorId,
            'to_user_id'  => $card->generated_by,
            'wallet_id'   => $wallet->id,
            'amount'      => $amount,
            'description' => "\${$amount} refunded to the generator's online wallet (wallet entry #{$wallet->id}).",
        ]);
    }

    // =========================================================================
    // Access, downloads, history
    // =========================================================================

    public function canView(User $user, DigitalCard $card): bool
    {
        return (int) $card->generated_by === (int) $user->id
            || ($card->recipient_id !== null && (int) $card->recipient_id === (int) $user->id)
            || $user->hasRole('admin');
    }

    public function recordDownload(DigitalCard $card, User $user, string $format): void
    {
        $this->logEvent($card, DigitalCardEvent::DOWNLOADED, [
            'actor_id'    => $user->id,
            'description' => 'Downloaded as ' . strtoupper($format) . '.',
            'meta'        => ['format' => $format],
        ]);
    }

    /**
     * History as shown to a member: everything except other people's failed
     * attempts and download noise (admins see the full trail).
     */
    public function historyFor(DigitalCard $card, User $viewer)
    {
        $query = $card->events()->with(['actor:id,name,username', 'fromUser:id,name,username', 'toUser:id,name,username', 'instalment:id,instalment_number']);

        if (!$viewer->hasRole('admin')) {
            $query->where(function ($q) use ($viewer) {
                $q->whereNotIn('event', [DigitalCardEvent::VERIFICATION_FAILED, DigitalCardEvent::DOWNLOADED])
                  ->orWhere('actor_id', $viewer->id);
            });
        }

        return $query->get();
    }

    public function userHasSavingPlan(User $user): bool
    {
        return $user->account_type === 'saving'
            || ($user->saving_enrolled && $user->saving_enrollment_activated && $user->savingInstalments()->exists());
    }

    public function onlineBalance(int $userId): float
    {
        return (float) Wallet::where('wallet_type', 'online')->where('user_id', $userId)->sum('balance');
    }

    // =========================================================================
    // Internals
    // =========================================================================

    /** Apply a status change, refusing anything not in DigitalCard::TRANSITIONS. */
    private function transition(DigitalCard $card, string $to, array $attributes = []): void
    {
        if (!$card->canTransitionTo($to)) {
            throw new DigitalCardException("Card cannot move from '{$card->status}' to '{$to}'.", 'invalid_transition');
        }

        $card->update(array_merge($attributes, ['status' => $to]));
    }

    /** @param DigitalCard|int|null $card */
    public function logEvent($card, string $event, array $data = []): DigitalCardEvent
    {
        $cardId = $card instanceof DigitalCard ? $card->id : $card;

        return DigitalCardEvent::create(array_merge([
            'digital_card_id' => $cardId,
            'event'           => $event,
            'ip_address'      => app()->runningInConsole() ? null : Request::ip(),
        ], $data));
    }

    /** 16 digits, cryptographically random, Luhn check digit, unique. */
    private function newCardNumber(): string
    {
        do {
            $digits = (string) random_int(1, 9);
            for ($i = 0; $i < 14; $i++) {
                $digits .= random_int(0, 9);
            }
            $number = $digits . $this->luhnCheckDigit($digits);
        } while (DigitalCard::where('card_number', $number)->exists());

        return $number;
    }

    private function luhnCheckDigit(string $partial): int
    {
        $sum    = 0;
        $double = true; // rightmost digit of the partial gets doubled
        for ($i = strlen($partial) - 1; $i >= 0; $i--) {
            $d = (int) $partial[$i];
            if ($double) {
                $d *= 2;
                if ($d > 9) {
                    $d -= 9;
                }
            }
            $sum   += $d;
            $double = !$double;
        }

        return (10 - ($sum % 10)) % 10;
    }

    private function newPin(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
