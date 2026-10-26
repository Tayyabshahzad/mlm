<?php

use App\Exceptions\DigitalCardException;
use App\Models\DigitalCard;
use App\Models\DigitalCardEvent;
use App\Models\SavingInstalment;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\DigitalCardReceivedNotification;
use App\Services\DigitalCardService;
use App\Services\SavingAccountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function dcUser(array $attrs = []): User
{
    static $n = 0;
    $n++;

    $user = User::factory()->create(array_merge([
        'username'       => 'dcuser' . $n . '_' . uniqid(),
        'transaction_id' => 'TEST-' . uniqid(),
        'phone_number'   => '0300' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login'      => true,
        'blocked'        => false,
    ], $attrs));

    // The portal layout expects every member to have a referral link.
    \App\Models\ReferralLink::create(['user_id' => $user->id, 'link' => $user->username]);

    return $user;
}

function dcSaver(array $attrs = []): User
{
    $user = dcUser(array_merge([
        'account_type'                  => 'saving',
        'saving_registration_completed' => true,
        'saving_plan_start_date'        => now()->toDateString(),
    ], $attrs));

    foreach ([1, 2, 3] as $i) {
        SavingInstalment::create([
            'user_id'           => $user->id,
            'instalment_number' => $i,
            'amount'            => 19,
            'due_date'          => now()->addMonths($i - 1)->toDateString(),
            'status'            => $i === 1 ? 'confirmed' : 'pending',
        ]);
    }

    return $user;
}

function dcLink(User $ancestor, User $descendant, int $level = 1, string $tree = 'saving'): void
{
    DB::table('referral_trees')->insert([
        'ancestor_id' => $ancestor->id, 'descendant_id' => $descendant->id, 'level' => $level, 'tree_type' => $tree,
    ]);
}

function dcFund(User $user, float $amount): void
{
    Wallet::create([
        'user_id' => $user->id, 'wallet_type' => 'online', 'balance' => $amount,
        'commission_type' => 'online', 'level' => 'online', 'transaction_type' => 'credit',
    ]);
}

function dcService(): DigitalCardService
{
    return app(DigitalCardService::class);
}

function dcOnline(User $user): float
{
    return round((float) Wallet::where('user_id', $user->id)->where('wallet_type', 'online')->sum('balance'), 2);
}

beforeEach(function () {
    Notification::fake();
    // Never run against the development database.
    expect(DB::connection()->getDatabaseName())->not->toBe('oracle_18');
});

// ─── Generation ──────────────────────────────────────────────────────────────

it('generates a card and debits the online wallet atomically', function () {
    $a = dcUser();
    dcFund($a, 100);

    $card = dcService()->generate($a, 19);

    expect($card->status)->toBe(DigitalCard::STATUS_AVAILABLE)
        ->and(strlen($card->card_number))->toBe(16)
        ->and(strlen($card->pin))->toBe(4)
        ->and((float) $card->amount)->toBe(19.0)
        ->and($card->expires_at->isFuture())->toBeTrue()
        ->and(dcOnline($a))->toBe(81.0);

    $debit = Wallet::find($card->generation_wallet_id);
    expect((float) $debit->balance)->toBe(-19.0)->and($debit->wallet_type)->toBe('online');

    expect(DigitalCardEvent::where('digital_card_id', $card->id)->where('event', 'generated')->exists())->toBeTrue();

    // PIN is encrypted at rest
    expect(DB::table('digital_cards')->where('id', $card->id)->value('pin'))->not->toBe($card->pin);
});

it('rejects generation with insufficient balance and changes nothing', function () {
    $a = dcUser();
    dcFund($a, 10);

    expect(fn () => dcService()->generate($a, 19))->toThrow(DigitalCardException::class, 'Insufficient');
    expect(DigitalCard::count())->toBe(0)->and(dcOnline($a))->toBe(10.0);
});

it('rejects generation for a member with negative points', function () {
    $a = dcUser(['negative_pv' => 5]);
    dcFund($a, 100);

    expect(fn () => dcService()->generate($a, 19))->toThrow(DigitalCardException::class);
    expect(DigitalCard::count())->toBe(0);
});

it('generates unique, Luhn-valid card numbers', function () {
    $a = dcUser();
    dcFund($a, 1000);

    $numbers = collect(range(1, 20))->map(fn () => dcService()->generate($a, 1)->card_number);

    expect($numbers->unique()->count())->toBe(20);
    $numbers->each(function ($n) {
        $sum = 0;
        foreach (str_split(strrev($n)) as $i => $d) {
            $d = (int) $d;
            if ($i % 2 === 1) { $d *= 2; if ($d > 9) $d -= 9; }
            $sum += $d;
        }
        expect($sum % 10)->toBe(0);
    });
});

// ─── Sharing ─────────────────────────────────────────────────────────────────

it('shares a card with an eligible downline and notifies them', function () {
    $a = dcUser();
    $b = dcSaver();
    dcLink($a, $b, 3, 'standard'); // any depth, either tree
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);
    dcService()->share($card, $a, $b, 'email');
    $card->refresh();

    expect($card->status)->toBe(DigitalCard::STATUS_SHARED)
        ->and($card->recipient_id)->toBe($b->id)
        ->and($card->share_method)->toBe('email')
        ->and($card->shared_at)->not->toBeNull();

    Notification::assertSentTo($b, DigitalCardReceivedNotification::class, fn ($n, $channels) => in_array('mail', $channels) && in_array('database', $channels));
});

it('refuses to share with someone outside the downline', function () {
    $a = dcUser();
    $stranger = dcSaver();
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);

    expect(fn () => dcService()->share($card, $a, $stranger, 'portal'))->toThrow(DigitalCardException::class, 'yourself or one of your downline');
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_AVAILABLE);
});

it('refuses to share with an upline', function () {
    $upline = dcSaver();
    $a = dcUser();
    dcLink($upline, $a);
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);
    expect(fn () => dcService()->share($card, $a, $upline, 'portal'))->toThrow(DigitalCardException::class);
});

it('refuses to share a downline member who has no instalment plan', function () {
    $a = dcUser();
    $b = dcUser();
    dcLink($a, $b);
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);
    expect(fn () => dcService()->share($card, $a, $b, 'portal'))->toThrow(DigitalCardException::class);
});

it('does not allow re-sharing an already shared card', function () {
    $a = dcUser();
    $b = dcSaver();
    $c = dcSaver();
    dcLink($a, $b);
    dcLink($a, $c);
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);
    dcService()->share($card, $a, $b, 'portal');

    expect(fn () => dcService()->share($card, $a, $c, 'portal'))->toThrow(DigitalCardException::class);
    expect($card->fresh()->recipient_id)->toBe($b->id);
});

it('does not let a non-generator share a card', function () {
    $a = dcUser();
    $x = dcUser();
    $b = dcSaver();
    dcLink($x, $b);
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);
    expect(fn () => dcService()->share($card, $x, $b, 'portal'))->toThrow(DigitalCardException::class);
});

// ─── Paying an instalment ────────────────────────────────────────────────────

function dcSharedCard(float $amount = 19): array
{
    $a = dcUser();
    $b = dcSaver();
    dcLink($a, $b);
    dcFund($a, 100);
    $card = dcService()->generate($a, $amount);
    dcService()->share($card, $a, $b, 'portal');

    return [$a, $b, $card->fresh()];
}

it('runs the full workflow: generate → share → pay → admin confirm → used', function () {
    [$a, $b, $card] = dcSharedCard();
    $admin = dcUser();

    $inst = dcService()->payInstalment($b, $card->reference, $card->pin);

    expect($inst->instalment_number)->toBe(2)
        ->and($inst->status)->toBe('submitted')
        ->and($inst->payment_method)->toBe('digital_card');
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_RESERVED);

    app(SavingAccountService::class)->confirmAndDeposit($inst->fresh(), $admin->id);

    $card->refresh();
    expect($card->status)->toBe(DigitalCard::STATUS_USED)
        ->and($card->used_at)->not->toBeNull()
        ->and($inst->fresh()->status)->toBe('confirmed');

    // Saving wallet credited exactly once for this instalment
    expect((float) Wallet::where('user_id', $b->id)->where('wallet_type', 'saving')->sum('balance'))->toBe(19.0);

    $events = DigitalCardEvent::where('digital_card_id', $card->id)->orderBy('id')->pluck('event')->all();
    expect($events)->toBe(['generated', 'shared', 'payment_submitted', 'payment_confirmed']);
});

it('cannot be used again after it has been used', function () {
    [$a, $b, $card] = dcSharedCard();
    $inst = dcService()->payInstalment($b, $card->reference, $card->pin);
    app(SavingAccountService::class)->confirmAndDeposit($inst->fresh(), dcUser()->id);

    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))
        ->toThrow(DigitalCardException::class, 'Used');

    expect(SavingInstalment::where('user_id', $b->id)->where('instalment_number', 3)->value('status'))->toBe('pending');
});

it('cannot be used twice while pending review', function () {
    [$a, $b, $card] = dcSharedCard();
    dcService()->payInstalment($b, $card->reference, $card->pin);

    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))->toThrow(DigitalCardException::class);
    expect(SavingInstalment::where('user_id', $b->id)->where('status', 'submitted')->count())->toBe(1);
});

it('rejects use by the generator or any other user and logs the attempt', function () {
    [$a, $b, $card] = dcSharedCard();
    $other = dcSaver();

    expect(fn () => dcService()->payInstalment($a, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'not assigned to you');
    expect(fn () => dcService()->payInstalment($other, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'not assigned to you');

    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_SHARED);
    expect(DigitalCardEvent::where('digital_card_id', $card->id)->where('event', 'verification_failed')->count())->toBe(2);
    expect(SavingInstalment::where('user_id', $other->id)->where('status', 'submitted')->exists())->toBeFalse();
});

it('rejects an unknown card id and logs it', function () {
    $b = dcSaver();

    expect(fn () => dcService()->payInstalment($b, '01JZZZZZZZZZZZZZZZZZZZZZZZ', '1234'))->toThrow(DigitalCardException::class, 'not found');
    expect(DigitalCardEvent::whereNull('digital_card_id')->where('event', 'verification_failed')->count())->toBe(1);
});

it('rejects a wrong PIN and locks after repeated failures', function () {
    [$a, $b, $card] = dcSharedCard();
    $wrong = $card->pin === '0000' ? '1111' : '0000';

    for ($i = 0; $i < 5; $i++) {
        expect(fn () => dcService()->payInstalment($b, $card->reference, $wrong))->toThrow(DigitalCardException::class, 'Incorrect');
    }
    // Even the correct PIN is now refused for a while
    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'Too many');
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_SHARED);
});

it('requires the card amount to match the instalment exactly', function () {
    [$a, $b, $card] = dcSharedCard(20);

    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'does not match');
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_SHARED);
});

it('includes ADB/FISP charges in the exact amount', function () {
    $a = dcUser();
    $b = dcSaver(['adb_option' => true]);
    dcLink($a, $b);
    dcFund($a, 100);
    $required = app(SavingAccountService::class)->requiredInstalmentTotal(
        app(SavingAccountService::class)->nextDueInstalment($b)
    );
    expect($required)->toBeGreaterThan(19.0);
    $card = dcService()->generate($a, $required);
    dcService()->share($card, $a, $b, 'portal');

    $inst = dcService()->payInstalment($b, $card->reference, $card->fresh()->pin);
    expect($inst->status)->toBe('submitted');
});

it('rejects an expired card', function () {
    [$a, $b, $card] = dcSharedCard();
    $card->forceFill(['expires_at' => now()->subMinute()])->save();

    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'expired');
});

// ─── Admin reject / reversal ─────────────────────────────────────────────────

it('returns the card to the recipient when admin rejects the instalment', function () {
    [$a, $b, $card] = dcSharedCard();
    $admin = dcUser();
    Role::findOrCreate('admin');
    $admin->assignRole('admin');

    $inst = dcService()->payInstalment($b, $card->reference, $card->pin);

    $this->actingAs($admin)
        ->post(route('admin.saving.reject', $inst), ['notes' => 'Wrong instalment'])
        ->assertRedirect();

    expect($inst->fresh()->status)->toBe('pending');
    $card->refresh();
    expect($card->status)->toBe(DigitalCard::STATUS_SHARED)->and($card->saving_instalment_id)->toBeNull();

    // And it can be used again
    expect(dcService()->payInstalment($b, $card->reference, $card->pin)->status)->toBe('submitted');
});

it('blocks admin confirmation of a card payment whose card is no longer reserved', function () {
    [$a, $b, $card] = dcSharedCard();
    $inst = dcService()->payInstalment($b, $card->reference, $card->pin);

    // Simulate tampering: card detached from the instalment
    DB::table('digital_cards')->where('id', $card->id)->update(['saving_instalment_id' => null]);

    expect(fn () => app(SavingAccountService::class)->confirmAndDeposit($inst->fresh(), dcUser()->id))
        ->toThrow(RuntimeException::class);

    expect($inst->fresh()->status)->toBe('submitted');
    expect(Wallet::where('user_id', $b->id)->where('wallet_type', 'saving')->exists())->toBeFalse();
});

it('restores the card when a confirmed instalment is reversed', function () {
    [$a, $b, $card] = dcSharedCard();
    $admin = dcUser();
    Role::findOrCreate('admin');
    $admin->assignRole('admin');

    $inst = dcService()->payInstalment($b, $card->reference, $card->pin);
    app(SavingAccountService::class)->confirmAndDeposit($inst->fresh(), $admin->id);

    $this->actingAs($admin)
        ->post(route('admin.saving.reverse', $inst), ['reason' => 'Posted to wrong member'])
        ->assertRedirect();

    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_SHARED);
    expect(DigitalCardEvent::where('digital_card_id', $card->id)->where('event', 'payment_reversed')->exists())->toBeTrue();
});

// ─── Cancel / expiry ─────────────────────────────────────────────────────────

it('lets the generator cancel an unshared card with a refund, but not a shared one', function () {
    $a = dcUser();
    dcFund($a, 50);
    $card = dcService()->generate($a, 19);

    dcService()->cancelByGenerator($card, $a);
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_CANCELLED)->and(dcOnline($a))->toBe(50.0);

    [$a2, $b2, $shared] = dcSharedCard();
    expect(fn () => dcService()->cancelByGenerator($shared, $a2))->toThrow(DigitalCardException::class);
});

it('expires unused cards and refunds the generator exactly once', function () {
    [$a, $b, $card] = dcSharedCard();
    $before = dcOnline($a);
    $card->forceFill(['expires_at' => now()->subMinute()])->save();

    expect(dcService()->expireDueCards())->toBe(1);
    expect(dcService()->expireDueCards())->toBe(0);

    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_EXPIRED)
        ->and(dcOnline($a))->toBe($before + 19.0);
});

it('never expires a card that is pending admin review', function () {
    [$a, $b, $card] = dcSharedCard();
    dcService()->payInstalment($b, $card->reference, $card->pin);
    $card->forceFill(['expires_at' => now()->subMinute()])->save();

    expect(dcService()->expireDueCards())->toBe(0);
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_RESERVED);
});

it('keeps the history append-only', function () {
    [$a, $b, $card] = dcSharedCard();
    $event = DigitalCardEvent::where('digital_card_id', $card->id)->first();

    expect(fn () => $event->update(['description' => 'tampered']))->toThrow(LogicException::class);
    expect(fn () => $event->delete())->toThrow(LogicException::class);
});

// ─── HTTP authorisation ──────────────────────────────────────────────────────

it('hides a card from users who are neither generator, recipient nor admin', function () {
    [$a, $b, $card] = dcSharedCard();
    $stranger = dcUser();

    $this->actingAs($stranger)->get(route('cards.show', $card))->assertNotFound();
    $this->actingAs($stranger)->get(route('cards.image', $card))->assertNotFound();
    $this->actingAs($stranger)->get(route('cards.pdf', $card))->assertNotFound();

    $this->actingAs($b)->get(route('cards.image', $card))->assertOk()->assertHeader('Content-Type', 'image/png');
    $this->actingAs($a)->get(route('cards.image', $card))->assertOk();
});

it('rejects payment through the HTTP endpoint for a non-recipient', function () {
    [$a, $b, $card] = dcSharedCard();
    $other = dcSaver();

    $this->actingAs($other)
        ->post(route('cards.pay'), ['card_reference' => $card->reference, 'pin' => $card->pin])
        ->assertSessionHas('error');

    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_SHARED);
});

it('pays through the HTTP endpoint for the recipient', function () {
    [$a, $b, $card] = dcSharedCard();

    $this->actingAs($b)
        ->post(route('cards.pay'), ['card_reference' => $card->reference, 'pin' => $card->pin])
        ->assertRedirect(route('saving.user.instalments'))
        ->assertSessionHas('success');

    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_RESERVED);
});

it('downloads do not change ownership or status', function () {
    [$a, $b, $card] = dcSharedCard();

    $this->actingAs($b)->get(route('cards.image', ['card' => $card, 'download' => 1]))->assertOk();
    $this->actingAs($b)->get(route('cards.pdf', $card))->assertOk();

    $fresh = $card->fresh();
    expect($fresh->status)->toBe(DigitalCard::STATUS_SHARED)
        ->and($fresh->recipient_id)->toBe($b->id)
        ->and(DigitalCard::count())->toBe(1)
        ->and(DigitalCardEvent::where('digital_card_id', $card->id)->where('event', 'downloaded')->count())->toBe(2);
});

it('lists only eligible downline members as recipients', function () {
    $a = dcUser();
    $down = dcSaver(['name' => 'Downline Saver']);
    $noPlan = dcUser(['name' => 'Downline NoPlan']);
    $outsider = dcSaver(['name' => 'Outsider Saver']);
    dcLink($a, $down, 2);
    dcLink($a, $noPlan, 1);

    $ids = collect($this->actingAs($a)->getJson(route('cards.recipients'))->json('results'))->pluck('id');

    expect($ids->all())->toBe([$down->id]);
});

// ─── Page rendering smoke test ───────────────────────────────────────────────

it('renders all digital card pages for members and admins', function () {
    [$a, $b, $card] = dcSharedCard();
    Role::findOrCreate('admin');
    $admin = dcUser();
    $admin->assignRole('admin');

    // Notifications are faked in beforeEach, so store the in-portal alert directly.
    $b->notifications()->create([
        'id'   => (string) \Illuminate\Support\Str::uuid(),
        'type' => DigitalCardReceivedNotification::class,
        'data' => ['card_reference' => $card->reference, 'message' => 'x'],
    ]);

    $this->actingAs($a)->get(route('cards.index'))->assertOk()->assertSee($card->maskedNumber());
    $this->actingAs($a)->get(route('cards.show', $card))->assertOk()->assertSee('Card History');
    $this->actingAs($b)->get(route('cards.received'))->assertOk()->assertSee('You have received a new card from');
    $this->actingAs($b)->get(route('cards.show', $card))->assertOk()->assertSee('Pay Instalment #2');
    $this->actingAs($b)->get(route('saving.user.instalments'))->assertOk()->assertSee('Pay with Card');

    dcService()->payInstalment($b, $card->reference, $card->pin);

    $this->actingAs($admin)->get(route('admin.cards.index'))->assertOk()->assertSee($card->reference);
    $this->actingAs($admin)->get(route('admin.cards.show', $card))->assertOk()->assertSee('Complete Audit Trail');
    $this->actingAs($admin)->get(route('admin.cards.failed'))->assertOk();
    $this->actingAs($admin)->get(route('admin.saving.pending'))->assertOk()->assertSee('Digital Card');

    // Members cannot reach admin screens
    $this->actingAs($a)->get(route('admin.cards.index'))->assertForbidden();
});

it('renders the email with the card image attached and no PIN in the body', function () {
    [$a, $b, $card] = dcSharedCard();
    $card->pin = 'PINSENTINEL'; // in memory only, so a leak would be unmistakable

    $mail = (new DigitalCardReceivedNotification($card, $a, true))->toMail($b);
    $html = (string) $mail->render();

    expect($html)->toContain($card->maskedNumber())
        ->and($html)->toContain(route('cards.show', $card))
        ->and($html)->not->toContain('PINSENTINEL')
        ->and($mail->rawAttachments)->toHaveCount(1)
        ->and(substr($mail->rawAttachments[0]['data'], 1, 3))->toBe('PNG');
});

it('rejects any use of an unassigned card — even with the correct number and PIN', function () {
    $a = dcUser();
    dcFund($a, 50);
    $card = dcService()->generate($a, 19); // generated, never shared

    $finder  = dcSaver(); // someone who obtained the image / number / PIN
    $selfUse = dcSaver(); // generator who is also a saving member
    dcFund($selfUse, 50);
    $ownCard = dcService()->generate($selfUse, 19);

    expect(fn () => dcService()->payInstalment($finder, $card->reference, $card->pin))
        ->toThrow(DigitalCardException::class, 'not assigned to you');
    expect(fn () => dcService()->payInstalment($selfUse, $ownCard->reference, $ownCard->pin))
        ->toThrow(DigitalCardException::class, 'not assigned to you');

    // Through the HTTP endpoint too
    $this->actingAs($finder)
        ->post(route('cards.pay'), ['card_reference' => $card->reference, 'pin' => $card->pin])
        ->assertSessionHas('error');

    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_AVAILABLE)
        ->and($ownCard->fresh()->status)->toBe(DigitalCard::STATUS_AVAILABLE)
        ->and(SavingInstalment::where('status', 'submitted')->count())->toBe(0)
        ->and(DigitalCardEvent::where('event', 'verification_failed')->count())->toBe(3);

    // The finder cannot even open or download it
    $this->actingAs($finder)->get(route('cards.show', $card))->assertNotFound();
    $this->actingAs($finder)->get(route('cards.image', $card))->assertNotFound();
});

it('shows the Generate Digital Card option on the online wallet page', function () {
    $a = dcUser();
    dcFund($a, 50);
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);

    $this->actingAs($a)->get(route('wallets.online'))
        ->assertOk()
        ->assertSee('Generate Digital Card')
        ->assertSee(route('cards.store'), false);
});

it('makes new cards valid for one year', function () {
    $a = dcUser();
    dcFund($a, 50);

    $card = dcService()->generate($a, 19);

    expect((int) round(now()->diffInDays($card->expires_at)))->toBe(365)
        ->and(DigitalCard::validityLabel())->toBe('1 year');

    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $this->actingAs($a)->get(route('wallets.online'))->assertOk()->assertSee('Valid for 1 year');
});

// ─── Card Settings (admin, stored in DB) ─────────────────────────────────────

function dcAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = dcUser();
    $admin->assignRole('admin');

    return $admin;
}

function dcSettingsPayload(array $override = []): array
{
    return array_merge([
        'digital_card_enabled'                 => '1',
        'digital_card_min_amount'              => '5',
        'digital_card_max_amount'              => '500',
        'digital_card_expiry_days'             => '90',
        'digital_card_max_failed_pin_attempts' => '2',
        'digital_card_pin_lock_minutes'        => '30',
    ], $override);
}

it('lets an admin update card settings, stores them in the DB and audits the change', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $admin = dcAdmin();

    $this->actingAs($admin)->get(route('admin.cards.settings'))->assertOk()->assertSee('Card Settings');
    $this->actingAs($admin)->post(route('admin.cards.settings.update'), dcSettingsPayload())->assertSessionHas('success');

    $s = \App\Models\Setting::first();
    expect((float) $s->digital_card_min_amount)->toBe(5.0)
        ->and((float) $s->digital_card_max_amount)->toBe(500.0)
        ->and((int) $s->digital_card_expiry_days)->toBe(90)
        ->and((int) $s->digital_card_max_failed_pin_attempts)->toBe(2)
        ->and((int) $s->digital_card_pin_lock_minutes)->toBe(30);

    $audit = DigitalCardEvent::where('event', DigitalCardEvent::SETTINGS_UPDATED)->first();
    expect($audit)->not->toBeNull()
        ->and($audit->actor_id)->toBe($admin->id)
        ->and($audit->meta)->toHaveKey('digital_card_expiry_days');

    // Generation now follows the DB settings
    $a = dcUser();
    dcFund($a, 1000);
    expect(fn () => dcService()->generate($a, 4))->toThrow(DigitalCardException::class, 'between');
    expect(fn () => dcService()->generate($a, 600))->toThrow(DigitalCardException::class, 'between');
    $card = dcService()->generate($a, 19);
    expect((int) round(now()->diffInDays($card->expires_at)))->toBe(90)
        ->and(DigitalCard::validityLabel())->toBe('90 days');
});

it('rejects invalid settings and blocks non-admins', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);

    $this->actingAs(dcAdmin())
        ->post(route('admin.cards.settings.update'), dcSettingsPayload(['digital_card_max_amount' => '2']))
        ->assertSessionHasErrors('digital_card_max_amount');

    $member = dcUser();
    $this->actingAs($member)->get(route('admin.cards.settings'))->assertForbidden();
    $this->actingAs($member)->post(route('admin.cards.settings.update'), dcSettingsPayload())->assertForbidden();
    expect((int) \App\Models\Setting::first()->digital_card_expiry_days)->toBe(365);
});

it('disables card generation from settings', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $this->actingAs(dcAdmin())->post(route('admin.cards.settings.update'), dcSettingsPayload(['digital_card_enabled' => null]));

    $a = dcUser();
    dcFund($a, 100);
    expect(fn () => dcService()->generate($a, 19))->toThrow(DigitalCardException::class, 'disabled');
    $this->actingAs($a)->get(route('wallets.online'))->assertOk()->assertDontSee('Generate Digital Card');
});

it('applies the PIN lock limit from settings', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $this->actingAs(dcAdmin())->post(route('admin.cards.settings.update'), dcSettingsPayload(['digital_card_min_amount' => '1']));

    [$a, $b, $card] = dcSharedCard();
    $wrong = $card->pin === '0000' ? '1111' : '0000';
    for ($i = 0; $i < 2; $i++) {
        expect(fn () => dcService()->payInstalment($b, $card->reference, $wrong))->toThrow(DigitalCardException::class, 'Incorrect');
    }
    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'Too many');
});

it('uploads a custom card logo and can restore the default', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $admin = dcAdmin();

    $this->actingAs($admin)->post(route('admin.cards.settings.update'), dcSettingsPayload([
        'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.png', 600, 100),
    ]))->assertSessionHas('success');

    $path = \App\Models\Setting::first()->digital_card_logo;
    expect($path)->toStartWith('uploads/digital-cards/')
        ->and(is_file(public_path($path)))->toBeTrue()
        ->and(\App\Support\DigitalCardSettings::logoPath())->toBe($path);

    // Card still renders with the custom logo
    [$a, $b, $card] = dcSharedCard();
    $this->actingAs($b)->get(route('cards.image', $card))->assertOk()->assertHeader('Content-Type', 'image/png');

    $this->actingAs($admin)->post(route('admin.cards.settings.update'), dcSettingsPayload(['remove_logo' => '1']));
    expect(\App\Models\Setting::first()->digital_card_logo)->toBeNull()
        ->and(is_file(public_path($path)))->toBeFalse()
        ->and(\App\Support\DigitalCardSettings::logoPath())->toBe('assets/custom-images/logo-white.png');
});

it('lets only an admin reset a PIN lock, after which the correct PIN works', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    [$a, $b, $card] = dcSharedCard();
    $admin = dcAdmin();
    $wrong = $card->pin === '0000' ? '1111' : '0000';

    for ($i = 0; $i < 5; $i++) {
        try { dcService()->payInstalment($b, $card->reference, $wrong); } catch (DigitalCardException) {}
    }
    expect(fn () => dcService()->payInstalment($b, $card->reference, $card->pin))->toThrow(DigitalCardException::class, 'Too many');

    // Admin sees the lock in Card Settings and on the card page
    $this->actingAs($admin)->get(route('admin.cards.settings'))->assertOk()->assertSee($card->maskedNumber())->assertSee('Reset Lock');
    $this->actingAs($admin)->get(route('admin.cards.show', $card))->assertOk()->assertSee('PIN Locked');

    // A member cannot reset it
    $this->actingAs($b)->post(route('admin.cards.reset-pin-lock', $card))->assertForbidden();
    expect(dcService()->pinLockStatus($card))->not->toBeNull();

    // Admin resets
    $this->actingAs($admin)
        ->post(route('admin.cards.reset-pin-lock', $card), ['reason' => 'Verified by phone'])
        ->assertSessionHas('success');

    expect(dcService()->pinLockStatus($card))->toBeNull();
    $this->actingAs($admin)->get(route('admin.cards.settings'))->assertSee('No cards are PIN-locked right now.');

    // History kept: 5 failures + reset event by the admin
    expect(DigitalCardEvent::where('digital_card_id', $card->id)->where('meta->reason', 'wrong_pin')->count())->toBe(5);
    $reset = DigitalCardEvent::where('digital_card_id', $card->id)->where('event', 'pin_lock_reset')->first();
    expect($reset->actor_id)->toBe($admin->id)->and($reset->description)->toContain('Verified by phone');

    // Correct PIN now works
    expect(dcService()->payInstalment($b, $card->reference, $card->pin)->status)->toBe('submitted');
});

it('counts wrong PINs afresh after a reset', function () {
    [$a, $b, $card] = dcSharedCard();
    $admin = dcAdmin();
    $wrong = $card->pin === '0000' ? '1111' : '0000';

    for ($i = 0; $i < 5; $i++) {
        try { dcService()->payInstalment($b, $card->reference, $wrong); } catch (DigitalCardException) {}
    }
    dcService()->resetPinLock($card, $admin);

    // 4 more wrong tries are not enough to lock again (limit 5)
    for ($i = 0; $i < 4; $i++) {
        expect(fn () => dcService()->payInstalment($b, $card->reference, $wrong))->toThrow(DigitalCardException::class, 'Incorrect');
    }
    expect(dcService()->pinLockStatus($card))->toBeNull();

    // The 5th locks it again
    try { dcService()->payInstalment($b, $card->reference, $wrong); } catch (DigitalCardException) {}
    expect(dcService()->pinLockStatus($card))->not->toBeNull();
});

it('shows the digital card on the admin saving-account page for card-paid instalments', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    [$a, $b, $card] = dcSharedCard();
    $admin = dcAdmin();

    $inst = dcService()->payInstalment($b, $card->reference, $card->pin);
    app(SavingAccountService::class)->confirmAndDeposit($inst->fresh(), $admin->id);

    $this->actingAs($admin)->get(route('admin.saving.show', $b))
        ->assertOk()
        ->assertSee('Digital Card')
        ->assertSee($card->maskedNumber())
        ->assertSee(route('admin.cards.show', $card), false);

    // And the card page links back to the instalment and shows it as used
    $this->actingAs($admin)->get(route('admin.cards.show', $card))
        ->assertOk()
        ->assertSee('#' . $inst->instalment_number)
        ->assertSee('Instalment Payment Confirmed');
});

it('lets a member on a saving plan assign their own card to themselves and pay their own instalment', function () {
    $a    = dcSaver();   // generator is on a saving plan
    $down = dcSaver();
    dcLink($a, $down);
    dcFund($a, 50);
    $card = dcService()->generate($a, 19);

    // Listed first in the recipient search, marked as "You"
    $results = collect($this->actingAs($a)->getJson(route('cards.recipients'))->json('results'));
    expect($results->first()['id'])->toBe($a->id)
        ->and($results->first()['text'])->toContain('You')
        ->and($results->pluck('id')->all())->toContain($down->id);

    // Assign to self through the HTTP endpoint — no "you received a card" alert to themselves
    $this->actingAs($a)->post(route('cards.share', $card), ['recipient_id' => $a->id, 'method' => 'email'])->assertSessionHas('success');
    Notification::assertNothingSentTo($a);

    $card->refresh();
    expect($card->status)->toBe(DigitalCard::STATUS_SHARED)
        ->and($card->recipient_id)->toBe($a->id)
        ->and(DigitalCardEvent::where('digital_card_id', $card->id)->where('event', 'shared')->value('to_user_id'))->toBe($a->id);

    // Pays their own next instalment, then the normal admin confirmation marks it used
    $inst = dcService()->payInstalment($a, $card->reference, $card->pin);
    expect($inst->user_id)->toBe($a->id)->and($inst->status)->toBe('submitted');

    app(SavingAccountService::class)->confirmAndDeposit($inst->fresh(), dcUser()->id);
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_USED);

    // Once assigned to themselves it cannot be re-assigned to the downline
    $card2 = dcService()->generate($a, 19);
    dcService()->share($card2, $a, $a, 'portal');
    expect(fn () => dcService()->share($card2, $a, $down, 'portal'))->toThrow(DigitalCardException::class);
});

it('does not let a member without a saving plan assign a card to themselves', function () {
    $a = dcUser();   // standard member, no instalments
    dcFund($a, 50);
    $card = dcService()->generate($a, 19);

    $ids = collect($this->actingAs($a)->getJson(route('cards.recipients'))->json('results'))->pluck('id');
    expect($ids->contains($a->id))->toBeFalse();

    expect(fn () => dcService()->share($card, $a, $a, 'portal'))->toThrow(DigitalCardException::class, 'yourself or one of your downline');
    expect($card->fresh()->status)->toBe(DigitalCard::STATUS_AVAILABLE);
});

it('lets a member with $1000 create ten $100 cards through the portal, and no more', function () {
    $a = dcUser();
    dcFund($a, 1000);

    for ($i = 1; $i <= 10; $i++) {
        $this->actingAs($a)->post(route('cards.store'), ['amount' => '100'])->assertSessionHas('success');
    }

    expect(DigitalCard::where('generated_by', $a->id)->count())->toBe(10)
        ->and(dcOnline($a))->toBe(0.0);

    // The 11th card is refused only because the balance is used up
    expect(fn () => dcService()->generate($a, 100))->toThrow(DigitalCardException::class, 'Insufficient');
});

// ─── Sidebar: Wallets menu layout ────────────────────────────────────────────

it('groups every wallet under one Wallets menu: Online first, then Saving and Standard as nested submenus', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);

    // Menu texts inside the Wallets <li class="... menu-wallets ...">, in order
    $walletsMenu = function (User $u) {
        $html = $this->actingAs($u)->get(route('cards.index'))->assertOk()->getContent();
        $start = strpos($html, 'menu-item-submenu menu-wallets');
        expect($start)->not->toBeFalse();
        $end  = strpos($html, 'Digital Cards', $start);
        preg_match_all('/<span class="menu-text">\s*([^<]+?)\s*</', substr($html, $start, $end - $start), $m);
        return [$m[1], $html];
    };

    $stdItems = ['Investment Wallet', 'Direct / Indirect', 'Rewards', 'ROI', 'Profit Share', 'Incentive Wallets'];

    // Standard member with saving access
    [$both, $html] = $walletsMenu(dcUser(['saving_enrolled' => true, 'saving_enrollment_activated' => true]));
    expect($both)->toBe(array_merge(
        ['Wallets', 'Online Wallet', 'Saving Wallet', 'Saving Account', 'Savings Direct / Indirect', 'Savings ROI', 'Saving ROI Report', 'Standard Wallet'],
        $stdItems, ['Transactions']));
    // No separate top-level Saving / Standard menus any more
    expect(substr_count($html, '<span class="menu-text">Saving Wallet</span>'))->toBe(1)
        ->and(substr_count($html, '<span class="menu-text">Standard Wallet</span>'))->toBe(1)
        ->and(substr_count($html, '<span class="menu-text">Online Wallet</span>'))->toBe(1);

    // Standard-only member
    [$std] = $walletsMenu(dcUser());
    expect($std)->toBe(array_merge(['Wallets', 'Online Wallet', 'Standard Wallet'], $stdItems, ['Transactions']));

    // Saving-only member
    [$sav] = $walletsMenu(dcSaver());
    expect($sav)->toBe(['Wallets', 'Online Wallet', 'Saving Wallet', 'Saving Account', 'Transactions']);
});

it('opens the Wallets menu and highlights the active wallet page', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $u = dcUser(['saving_enrolled' => true, 'saving_enrollment_activated' => true]);

    $html = $this->actingAs($u)->get(route('wallets.saving.direct.indirect'))->assertOk()->getContent();
    expect($html)->toContain('menu-wallets  menu-item-open')
        ->and($html)->toMatch('/menu-item\s+menu-item-active[^>]*>\s*<a href="[^"]*saving-direct-indirect"/');
});

it('shows the violet Saving Direct/Indirect → Online transfer option', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $u = dcUser(['saving_enrolled' => true, 'saving_enrollment_activated' => true]);

    // No balance yet: option shown but disabled
    $this->actingAs($u)->get(route('wallets.saving.direct.indirect'))->assertOk()
        ->assertSee('Transfer to Online Wallet')->assertSee('Nothing available to transfer yet.')
        ->assertSee('wv-option', false);

    // With saving commissions: enabled, shows Direct and Indirect amounts
    Wallet::create(['user_id' => $u->id, 'wallet_type' => 'direct_indirect', 'balance' => 7, 'direct_balance' => 7, 'indirect_balance' => 0,
        'total_amount' => 7, 'commission_type' => 'direct', 'level' => 1, 'source_type' => 'saving_instalment', 'wallet_from' => $u->id]);
    Wallet::create(['user_id' => $u->id, 'wallet_type' => 'direct_indirect', 'balance' => 2, 'direct_balance' => 0, 'indirect_balance' => 2,
        'total_amount' => 2, 'commission_type' => 'indirect', 'level' => 2, 'source_type' => 'saving_instalment', 'wallet_from' => $u->id]);

    $this->actingAs($u)->get(route('wallets.saving.direct.indirect'))->assertOk()
        ->assertSee('Saving Direct $7.00')->assertSee('Saving Indirect $2.00')
        ->assertDontSee('Nothing available to transfer yet.')
        ->assertSee('wv-btn', false);
});

// ─── Whole sidebar structure ────────────────────────────────────────────────

it('organises the admin sidebar into Member, Staff Tools and Administration sections without dead links', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    Role::findOrCreate('admin');
    $admin = dcUser();
    $admin->assignRole('admin');

    $html = $this->actingAs($admin)->get(route('cards.index'))->assertOk()->getContent();
    $aside = substr($html, strpos($html, 'begin::Menu Nav'), strpos($html, 'end::Menu Nav') - strpos($html, 'begin::Menu Nav'));
    preg_match_all('/<(?:span|h4) class="menu-text">\s*([^<]+?)\s*</', $aside, $m);
    $items = $m[1];

    // Sections in order
    $sections = array_values(array_intersect($items, ['Staff Tools', 'Administration', 'Session']));
    expect($sections)->toBe(['Staff Tools', 'Administration', 'Session']);

    // Admin groups and their pages, in order
    $after = fn (string $label, int $n) => array_slice($items, max(array_keys($items, $label)) + 1, $n); // last occurrence = admin group label
    expect($after('Members', 6))->toBe(['All Members', 'Saving Members', 'Deleted Members', 'Change Parent', 'Activation Codes', 'Staff Roles'])
        ->and($after('Finance', 5))->toBe(['Withdrawal Requests', 'Withdrawal Analytics', 'Account Top-ups', 'Top-up Analytics', 'Incentive Wallet'])
        ->and($after('ROI &amp; Rental', 4))->toBe(['ROI Monitoring', 'ROI Payments', 'Rental Percentage', 'ROI Reversal'])
        ->and($after('Rewards', 2))->toBe(['Pending Rewards', 'Reward Review'])
        ->and($after('Reports', 3))->toBe(['Genealogy Tree Report', 'Genealogy Tree View', 'Saving Account Tree'])
        ->and($after('Settings', 9))->toBe(['Basic Settings', 'ROI Settings', 'User Plans (Gold/Silver)', 'Commission &amp; Bonuses',
            'Profit Share Settings', 'Reward Settings', 'Saving Account Settings', 'Instalment Commission Rates', 'Card Settings']);
    expect($items)->toContain('Savings Program')->toContain('Saving Accounts')->toContain('Adjust Instalment Plan')
        ->toContain('Genealogy')->toContain('My Account')->toContain('Bank Reconciliation')->toContain('Upload Payment Sheet');

    // Removed: dead / duplicate / temporary entries
    expect($items)->not->toContain('Rank')->not->toContain('My Savings Tree')->not->toContain('Reward Review (Temp)')
        ->and(array_count_values($items)['Logout'])->toBe(1)
        ->and($aside)->not->toContain('href="@"')
        ->and($aside)->toContain('id="sidebar-logout-form"');

    // Every sidebar link resolves (no 404 / 500)
    preg_match_all('/href="(http[^"]+)"/', $aside, $links);
    foreach (array_unique($links[1]) as $url) {
        $status = $this->actingAs($admin)->get($url)->getStatusCode();
        expect($status)->toBeLessThan(500, "Sidebar link {$url} returned {$status}");
        expect($status)->not->toBe(404, "Sidebar link {$url} is broken");
    }
});

it('keeps the member sidebar grouped and hides the admin and staff sections from members', function () {
    \App\Models\Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
    $html = $this->actingAs(dcSaver())->get(route('cards.index'))->assertOk()->getContent();
    $aside = substr($html, strpos($html, 'begin::Menu Nav'), strpos($html, 'end::Menu Nav') - strpos($html, 'begin::Menu Nav'));
    preg_match_all('/<(?:span|h4) class="menu-text">\s*([^<]+?)\s*</', $aside, $m);

    expect($m[1])->toContain('Genealogy')->toContain('Wallets')->toContain('My Account')->toContain('My Saving Plan')->toContain('Logout')
        ->not->toContain('Administration')->not->toContain('Staff Tools')->not->toContain('Members')->not->toContain('Settings');
});
