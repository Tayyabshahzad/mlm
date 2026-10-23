<?php

namespace App\Services;

use App\Exceptions\ReconciliationException;
use App\Models\BankPaymentReconciliation;
use App\Models\InstalmentPaymentAudit;
use App\Models\SavingInstalment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Office Admin bank reconciliation for saving-plan instalments.
 *
 * The officer checks the bank statement, then either verifies a member's
 * submission or records a payment the member never submitted. Both paths end
 * in the existing SavingAccountService::confirmAndDeposit() — the same deposit,
 * ADB/FISP and commission logic the admin "Confirm" button uses.
 */
class BankReconciliationService
{
    public const PAYMENT_METHOD = 'bank';

    public function __construct(private SavingAccountService $savingAccountService) {}

    /**
     * Verify a member-submitted instalment against the bank statement and confirm it.
     *
     * @param array{bank_reference:string, amount:float|string, payment_date:string, bank_name?:?string, notes?:?string} $data
     */
    public function verifySubmission(SavingInstalment $instalment, User $officer, array $data): BankPaymentReconciliation
    {
        return $this->guarded($instalment, $officer, 'verify', function () use ($instalment, $officer, $data) {
            $inst = SavingInstalment::whereKey($instalment->id)->lockForUpdate()->firstOrFail();

            if ($inst->status !== 'submitted') {
                throw new ReconciliationException("Instalment #{$inst->instalment_number} is not awaiting verification (status: {$inst->status}).", 'wrong_status');
            }
            if ($inst->payment_method === DigitalCardService::PAYMENT_METHOD) {
                throw new ReconciliationException('This instalment was paid with a digital card, not a bank transfer. It is confirmed by the main admin.', 'digital_card');
            }

            $rec = $this->createReconciliation($inst, $officer, $data, BankPaymentReconciliation::SOURCE_USER_SUBMISSION, $inst->transaction_id);

            $this->savingAccountService->confirmAndDeposit(
                $inst, $officer->id,
                trim('Bank verified by office (ref ' . $rec->bank_reference . '). ' . ($data['notes'] ?? ''))
            );

            $this->audit(InstalmentPaymentAudit::VERIFIED, $officer, $inst, $rec,
                "Verified \${$rec->amount} in bank (ref {$rec->bank_reference}, {$rec->payment_date->format('d M Y')}) — instalment #{$inst->instalment_number} confirmed.",
                ['bank_reference' => $rec->bank_reference, 'amount' => (float) $rec->amount, 'payment_date' => $rec->payment_date->toDateString(),
                 'member_reference' => $rec->user_submitted_reference, 'bank_name' => $rec->bank_name]);

            return $rec;
        });
    }

    /**
     * Record and confirm a payment seen in the bank for an instalment the member
     * never submitted (e.g. cash / branch deposit).
     */
    public function recordPayment(SavingInstalment $instalment, User $officer, array $data, ?UploadedFile $proof = null): BankPaymentReconciliation
    {
        return $this->guarded($instalment, $officer, 'record', function () use ($instalment, $officer, $data, $proof) {
            $inst = SavingInstalment::whereKey($instalment->id)->lockForUpdate()->firstOrFail();

            if (!in_array($inst->status, ['pending', 'missed'], true)) {
                throw new ReconciliationException("Instalment #{$inst->instalment_number} is not unpaid (status: {$inst->status}).", 'wrong_status');
            }

            $rec = $this->createReconciliation($inst, $officer, $data, BankPaymentReconciliation::SOURCE_OFFICE_ENTRY, null);

            $inst->update([
                'status'           => 'submitted',
                'transaction_id'   => $rec->bank_reference,
                'payment_method'   => self::PAYMENT_METHOD,
                'submitted_amount' => $rec->amount,
                'submitted_at'     => now(),
            ]);

            if ($proof) {
                $inst->clearMediaCollection('instalment_proof');
                $inst->addMedia($proof)->toMediaCollection('instalment_proof');
            }

            $this->savingAccountService->confirmAndDeposit(
                $inst, $officer->id,
                trim('Payment recorded by office from bank statement (ref ' . $rec->bank_reference . '). ' . ($data['notes'] ?? ''))
            );

            $this->audit(InstalmentPaymentAudit::RECORDED, $officer, $inst, $rec,
                "Recorded \${$rec->amount} received in bank (ref {$rec->bank_reference}, {$rec->payment_date->format('d M Y')}) — instalment #{$inst->instalment_number} confirmed.",
                ['bank_reference' => $rec->bank_reference, 'amount' => (float) $rec->amount, 'payment_date' => $rec->payment_date->toDateString(),
                 'bank_name' => $rec->bank_name, 'proof_uploaded' => (bool) $proof]);

            return $rec;
        });
    }

    /** Member submitted a payment that is not in the bank — send it back to pending. */
    public function rejectSubmission(SavingInstalment $instalment, User $officer, string $reason): void
    {
        $this->guarded($instalment, $officer, 'reject', function () use ($instalment, $officer, $reason) {
            $inst = SavingInstalment::whereKey($instalment->id)->lockForUpdate()->firstOrFail();

            if ($inst->status !== 'submitted') {
                throw new ReconciliationException("Instalment #{$inst->instalment_number} is not awaiting verification.", 'wrong_status');
            }
            if ($inst->payment_method === DigitalCardService::PAYMENT_METHOD) {
                throw new ReconciliationException('Digital card payments are reviewed by the main admin.', 'digital_card');
            }

            $details = ['member_reference' => $inst->transaction_id, 'submitted_amount' => (float) $inst->submitted_amount, 'reason' => $reason];

            $this->savingAccountService->rejectSubmission($inst, $officer->id, 'Not received in bank — ' . $reason);

            $this->audit(InstalmentPaymentAudit::REJECTED, $officer, $inst, null,
                "Instalment #{$inst->instalment_number} rejected: payment not found in bank. {$reason}", $details);
        });
    }

    /**
     * Called when a confirmed instalment is reversed (admin reversal / console
     * rollback): frees the bank reference and records the reversal.
     */
    public function markReversed(SavingInstalment $instalment, ?int $actorId, ?string $reason = null): void
    {
        $rec = BankPaymentReconciliation::where('saving_instalment_id', $instalment->id)
            ->where('status', BankPaymentReconciliation::STATUS_VERIFIED)
            ->lockForUpdate()
            ->first();

        if (!$rec) {
            return;
        }

        $rec->update([
            'status'          => BankPaymentReconciliation::STATUS_REVERSED,
            'reference_key'   => null,
            'reversed_by'     => $actorId,
            'reversed_at'     => now(),
            'reversal_reason' => $reason,
        ]);

        InstalmentPaymentAudit::create([
            'action'               => InstalmentPaymentAudit::REVERSED,
            'actor_id'             => $actorId,
            'user_id'              => $instalment->user_id,
            'saving_instalment_id' => $instalment->id,
            'reconciliation_id'    => $rec->id,
            'description'          => "Instalment #{$instalment->instalment_number} reversed; bank ref {$rec->bank_reference} released." . ($reason ? " {$reason}" : ''),
            'details'              => ['bank_reference' => $rec->bank_reference, 'amount' => (float) $rec->amount],
            'ip_address'           => app()->runningInConsole() ? null : Request::ip(),
        ]);
    }

    /** Find where a bank reference is already used (active reconciliation or confirmed instalment). */
    public function referenceInUse(string $reference, ?int $exceptInstalmentId = null): ?string
    {
        $key = BankPaymentReconciliation::normaliseReference($reference);

        $rec = BankPaymentReconciliation::with('user:id,username')
            ->where('reference_key', $key)
            ->first();
        if ($rec) {
            return "already verified for @{$rec->user?->username} (instalment ID {$rec->saving_instalment_id})";
        }

        $inst = SavingInstalment::with('user:id,username')
            ->where('status', 'confirmed')
            ->when($exceptInstalmentId, fn ($q) => $q->where('id', '!=', $exceptInstalmentId))
            ->whereRaw("UPPER(REPLACE(transaction_id, ' ', '')) = ?", [$key])
            ->first();

        return $inst ? "already used on confirmed instalment #{$inst->instalment_number} of @{$inst->user?->username}" : null;
    }

    // ------------------------------------------------------------------------

    private function createReconciliation(SavingInstalment $inst, User $officer, array $data, string $source, ?string $memberRef): BankPaymentReconciliation
    {
        $reference = trim((string) ($data['bank_reference'] ?? ''));
        if ($reference === '') {
            throw new ReconciliationException('Bank transaction reference is required.', 'missing_reference');
        }

        $date = Carbon::parse($data['payment_date'] ?? null);
        if ($date->isFuture()) {
            throw new ReconciliationException('Payment date cannot be in the future.', 'future_date');
        }

        $amount   = round((float) ($data['amount'] ?? 0), 2);
        $required = $this->savingAccountService->requiredInstalmentTotal($inst);
        if ($amount !== round($required, 2)) {
            throw new ReconciliationException(
                'Amount received $' . number_format($amount, 2) . ' does not match the exact amount due for instalment #' .
                $inst->instalment_number . ' ($' . number_format($required, 2) . ').',
                'amount_mismatch'
            );
        }

        if ($where = $this->referenceInUse($reference, $inst->id)) {
            throw new ReconciliationException("Bank reference \"{$reference}\" is {$where}. One bank transaction cannot pay two instalments.", 'duplicate_reference');
        }

        try {
            return BankPaymentReconciliation::create([
                'saving_instalment_id'     => $inst->id,
                'user_id'                  => $inst->user_id,
                'bank_reference'           => $reference,
                'reference_key'            => BankPaymentReconciliation::normaliseReference($reference),
                'amount'                   => $amount,
                'payment_date'             => $date->toDateString(),
                'bank_name'                => $data['bank_name'] ?? null,
                'source'                   => $source,
                'user_submitted_reference' => $memberRef,
                'notes'                    => $data['notes'] ?? null,
                'status'                   => BankPaymentReconciliation::STATUS_VERIFIED,
                'verified_by'              => $officer->id,
                'verified_at'              => now(),
            ]);
        } catch (QueryException $e) {
            // Unique index caught a concurrent use of the same reference.
            if (($e->errorInfo[1] ?? null) == 1062) {
                throw new ReconciliationException("Bank reference \"{$reference}\" was just used on another instalment.", 'duplicate_reference');
            }
            throw $e;
        }
    }

    /**
     * Run an action in a transaction; if it is refused, record the blocked
     * attempt in the audit log (after rollback, so it is kept).
     */
    private function guarded(SavingInstalment $instalment, User $officer, string $operation, callable $work): mixed
    {
        try {
            return DB::transaction($work);
        } catch (ReconciliationException $e) {
            InstalmentPaymentAudit::create([
                'action'               => InstalmentPaymentAudit::BLOCKED,
                'actor_id'             => $officer->id,
                'user_id'              => $instalment->user_id,
                'saving_instalment_id' => $instalment->id,
                'description'          => ucfirst($operation) . ' blocked: ' . $e->getMessage(),
                'details'              => ['operation' => $operation, 'reason' => $e->reason],
                'ip_address'           => Request::ip(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error("Bank reconciliation {$operation} failed for instalment {$instalment->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function audit(string $action, User $officer, SavingInstalment $inst, ?BankPaymentReconciliation $rec, string $description, array $details): void
    {
        InstalmentPaymentAudit::create([
            'action'               => $action,
            'actor_id'             => $officer->id,
            'user_id'              => $inst->user_id,
            'saving_instalment_id' => $inst->id,
            'reconciliation_id'    => $rec?->id,
            'description'          => $description,
            'details'              => $details,
            'ip_address'           => Request::ip(),
        ]);
    }
}
