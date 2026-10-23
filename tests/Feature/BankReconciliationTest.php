<?php

use App\Exceptions\ReconciliationException;
use App\Models\BankPaymentReconciliation;
use App\Models\InstalmentPaymentAudit;
use App\Models\ReferralLink;
use App\Models\SavingInstalment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BankReconciliationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function brUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $user = User::factory()->create(array_merge([
        'username'       => 'bruser' . $n . '_' . uniqid(),
        'transaction_id' => 'TEST-' . uniqid(),
        'phone_number'   => '0311' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login'      => true,
        'blocked'        => false,
    ], $attrs));
    ReferralLink::create(['user_id' => $user->id, 'link' => $user->username]);

    return $user;
}

/** Saving member: #1 confirmed, #2 and #3 pending ($19 each). */
function brMember(array $attrs = []): User
{
    $user = brUser(array_merge([
        'account_type'                  => 'saving',
        'saving_registration_completed' => true,
        'saving_plan_start_date'        => now()->toDateString(),
    ], $attrs));

    foreach ([1, 2, 3] as $i) {
        SavingInstalment::create([
            'user_id' => $user->id, 'instalment_number' => $i, 'amount' => 19,
            'due_date' => now()->addMonths($i - 1)->toDateString(),
            'status' => $i === 1 ? 'confirmed' : 'pending',
        ]);
    }

    return $user;
}

function brSubmitted(User $member, string $ref = 'TXN-111', int $number = 2): SavingInstalment
{
    $inst = SavingInstalment::where('user_id', $member->id)->where('instalment_number', $number)->first();
    $inst->update(['status' => 'submitted', 'transaction_id' => $ref, 'payment_method' => 'bank', 'submitted_amount' => 19, 'submitted_at' => now()]);

    return $inst->fresh();
}

function brOfficer(): User
{
    Role::findOrCreate('office-admin');
    $u = brUser();
    $u->assignRole('office-admin');

    return $u;
}

function brAdmin(): User
{
    Role::findOrCreate('admin');
    $u = brUser();
    $u->assignRole('admin');

    return $u;
}

function brPay(array $override = []): array
{
    return array_merge([
        'bank_reference'   => 'MZN-778899',
        'amount'           => '19.00',
        'payment_date'     => now()->toDateString(),
        'bank_name'        => 'Meezan Bank',
        'confirm_verified' => '1',
    ], $override);
}

function brSvc(): BankReconciliationService
{
    return app(BankReconciliationService::class);
}

beforeEach(function () {
    Notification::fake();
    // Media uploads must never touch the real storage/app/public folder.
    \Illuminate\Support\Facades\Storage::fake('public');
    expect(DB::connection()->getDatabaseName())->not->toBe('oracle_18');
    Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
});

// ─── Verify a member submission ──────────────────────────────────────────────

it('lets an office admin verify a submitted payment and confirms it through the existing flow', function () {
    $member  = brMember();
    $inst    = brSubmitted($member, 'member-typed-123');
    $officer = brOfficer();

    $this->actingAs($officer)->post(route('reconciliation.verify', $inst), brPay())
        ->assertSessionHas('success');

    $inst->refresh();
    expect($inst->status)->toBe('confirmed')
        ->and((int) $inst->confirmed_by)->toBe($officer->id)
        ->and($inst->deposited_at)->not->toBeNull();

    // Existing deposit logic ran exactly once
    expect((float) Wallet::where('user_id', $member->id)->where('wallet_type', 'saving')->sum('balance'))->toBe(19.0);

    $rec = BankPaymentReconciliation::first();
    expect($rec->bank_reference)->toBe('MZN-778899')
        ->and($rec->reference_key)->toBe('MZN-778899')
        ->and($rec->user_submitted_reference)->toBe('member-typed-123')
        ->and((int) $rec->verified_by)->toBe($officer->id)
        ->and($rec->source)->toBe('user_submission')
        ->and((float) $rec->amount)->toBe(19.0);

    $audit = InstalmentPaymentAudit::where('action', 'bank_verified')->first();
    expect($audit->actor_id)->toBe($officer->id)->and($audit->saving_instalment_id)->toBe($inst->id);
});

it('requires the officer to tick the bank-statement confirmation', function () {
    $inst = brSubmitted(brMember());

    $this->actingAs(brOfficer())->post(route('reconciliation.verify', $inst), brPay(['confirm_verified' => null]))
        ->assertSessionHasErrors('confirm_verified');

    expect($inst->fresh()->status)->toBe('submitted');
});

it('blocks a wrong amount and logs the blocked attempt', function () {
    $inst = brSubmitted(brMember());

    expect(fn () => brSvc()->verifySubmission($inst, brOfficer(), brPay(['amount' => '18.50'])))
        ->toThrow(ReconciliationException::class, 'does not match');

    expect($inst->fresh()->status)->toBe('submitted')
        ->and(BankPaymentReconciliation::count())->toBe(0)
        ->and(InstalmentPaymentAudit::where('action', 'verification_blocked')->where('details->reason', 'amount_mismatch')->count())->toBe(1);
});

it('rejects a future payment date', function () {
    $inst = brSubmitted(brMember());

    $this->actingAs(brOfficer())->post(route('reconciliation.verify', $inst), brPay(['payment_date' => now()->addDay()->toDateString()]))
        ->assertSessionHasErrors('payment_date');
});

// ─── Duplicate bank transactions ─────────────────────────────────────────────

it('never lets one bank transaction pay two instalments (even with different spacing/case)', function () {
    $officer = brOfficer();
    $a = brSubmitted(brMember(), 'x');
    $b = brSubmitted(brMember(), 'y');

    brSvc()->verifySubmission($a, $officer, brPay(['bank_reference' => 'mzn 778899']));

    expect(fn () => brSvc()->verifySubmission($b, $officer, brPay(['bank_reference' => 'MZN778899'])))
        ->toThrow(ReconciliationException::class, 'cannot pay two instalments');

    expect($b->fresh()->status)->toBe('submitted')
        ->and(BankPaymentReconciliation::count())->toBe(1)
        ->and(InstalmentPaymentAudit::where('details->reason', 'duplicate_reference')->count())->toBe(1);
});

it('also blocks a reference already used on a confirmed instalment outside reconciliation', function () {
    $old = brMember();
    SavingInstalment::where('user_id', $old->id)->where('instalment_number', 1)->update(['transaction_id' => 'HBL-5555']);

    $inst = brSubmitted(brMember());
    expect(fn () => brSvc()->verifySubmission($inst, brOfficer(), brPay(['bank_reference' => 'hbl-5555'])))
        ->toThrow(ReconciliationException::class, 'already used');
});

it('enforces the unique reference at the database level', function () {
    $member = brMember();
    BankPaymentReconciliation::create([
        'saving_instalment_id' => SavingInstalment::where('user_id', $member->id)->value('id'), 'user_id' => $member->id,
        'bank_reference' => 'R1', 'reference_key' => 'R1', 'amount' => 19, 'payment_date' => now(), 'source' => 'office_entry',
        'verified_by' => $member->id, 'verified_at' => now(),
    ]);

    expect(fn () => BankPaymentReconciliation::create([
        'saving_instalment_id' => SavingInstalment::where('user_id', $member->id)->value('id'), 'user_id' => $member->id,
        'bank_reference' => 'R1', 'reference_key' => 'R1', 'amount' => 19, 'payment_date' => now(), 'source' => 'office_entry',
        'verified_by' => $member->id, 'verified_at' => now(),
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('cannot verify the same instalment twice', function () {
    $inst    = brSubmitted(brMember());
    $officer = brOfficer();

    brSvc()->verifySubmission($inst, $officer, brPay());
    expect(fn () => brSvc()->verifySubmission($inst, $officer, brPay(['bank_reference' => 'OTHER-1'])))
        ->toThrow(ReconciliationException::class, 'not awaiting');

    expect(BankPaymentReconciliation::count())->toBe(1)
        ->and((float) Wallet::where('user_id', $inst->user_id)->where('wallet_type', 'saving')->sum('balance'))->toBe(19.0);
});

// ─── Record a payment with no submission ─────────────────────────────────────

it('records a payment seen in the bank for an unpaid instalment', function () {
    $member  = brMember();
    $inst    = SavingInstalment::where('user_id', $member->id)->where('instalment_number', 2)->first();
    $officer = brOfficer();

    $this->actingAs($officer)->post(route('reconciliation.record', $inst), brPay([
        'proof' => \Illuminate\Http\UploadedFile::fake()->image('slip.jpg'),
    ]))->assertSessionHas('success');

    $inst->refresh();
    expect($inst->status)->toBe('confirmed')
        ->and($inst->transaction_id)->toBe('MZN-778899')
        ->and($inst->payment_method)->toBe('bank')
        ->and($inst->proofScreenshot())->not->toBeNull()
        ->and(BankPaymentReconciliation::first()->source)->toBe('office_entry')
        ->and(InstalmentPaymentAudit::where('action', 'bank_recorded')->count())->toBe(1);
});

it('refuses to record over an instalment that is already submitted or paid', function () {
    $member = brMember();
    $paid   = SavingInstalment::where('user_id', $member->id)->where('instalment_number', 1)->first();
    $sub    = brSubmitted($member);

    expect(fn () => brSvc()->recordPayment($paid, brOfficer(), brPay()))->toThrow(ReconciliationException::class, 'not unpaid');
    expect(fn () => brSvc()->recordPayment($sub, brOfficer(), brPay(['bank_reference' => 'Z9'])))->toThrow(ReconciliationException::class, 'not unpaid');
});

// ─── Reject ──────────────────────────────────────────────────────────────────

it('rejects a submission that is not in the bank and returns it to unpaid', function () {
    $inst    = brSubmitted(brMember(), 'FAKE-1');
    $officer = brOfficer();

    $this->actingAs($officer)->post(route('reconciliation.reject', $inst), ['reason' => 'No credit on 23 Oct statement'])
        ->assertSessionHas('success');

    $inst->refresh();
    expect($inst->status)->toBe('pending')
        ->and($inst->transaction_id)->toBeNull()
        ->and($inst->notes)->toContain('Not received in bank')
        ->and(InstalmentPaymentAudit::where('action', 'bank_not_received')->first()->details['member_reference'])->toBe('FAKE-1');
});

// ─── Digital-card payments stay with the main admin ──────────────────────────

it('does not handle digital-card payments', function () {
    $inst = brSubmitted(brMember());
    $inst->update(['payment_method' => 'digital_card']);

    expect(fn () => brSvc()->verifySubmission($inst, brOfficer(), brPay()))->toThrow(ReconciliationException::class, 'digital card');
    expect(fn () => brSvc()->rejectSubmission($inst, brOfficer(), 'x'))->toThrow(ReconciliationException::class, 'main admin');

    $this->actingAs(brOfficer())->get(route('reconciliation.index'))->assertOk()->assertSee('No submissions awaiting verification.');
});

// ─── Reversal frees the reference ────────────────────────────────────────────

it('marks the reconciliation reversed and frees the bank reference when an admin reverses the instalment', function () {
    $inst  = brSubmitted(brMember());
    brSvc()->verifySubmission($inst, brOfficer(), brPay());
    $admin = brAdmin();

    $this->actingAs($admin)->post(route('admin.saving.reverse', $inst), ['reason' => 'Matched to wrong member'])->assertRedirect();

    $rec = BankPaymentReconciliation::first();
    expect($rec->status)->toBe('reversed')->and($rec->reference_key)->toBeNull()
        ->and(InstalmentPaymentAudit::where('action', 'reconciliation_reversed')->count())->toBe(1);

    // The bank transaction can now be matched to the right member
    $right = brSubmitted(brMember());
    expect(brSvc()->verifySubmission($right, brOfficer(), brPay())->status)->toBe('verified');
});

// ─── Access control ──────────────────────────────────────────────────────────

it('restricts the office admin to bank reconciliation only', function () {
    $officer = brOfficer();
    $member  = brMember();

    foreach (['submitted', 'due', 'reconciled', 'audit'] as $tab) {
        $this->actingAs($officer)->get(route('reconciliation.index', ['tab' => $tab]))->assertOk();
    }

    $this->actingAs($officer)->get(route('admin.saving.index'))->assertForbidden();
    $this->actingAs($officer)->get(route('admin.cards.index'))->assertForbidden();
    $this->actingAs($officer)->get(route('admin.office-admins.index'))->assertForbidden();
    $this->actingAs($officer)->get(route('admin.saving.pending'))->assertForbidden();

    // Ordinary members cannot open reconciliation at all
    $this->actingAs($member)->get(route('reconciliation.index'))->assertForbidden();
    $inst = brSubmitted($member);
    $this->actingAs($member)->post(route('reconciliation.verify', $inst), brPay())->assertForbidden();
    expect($inst->fresh()->status)->toBe('submitted');
});

it('shows members, amounts and status on the reconciliation screens', function () {
    $member  = brMember(['name' => 'Bilal Recon']);
    brSubmitted($member, 'BILAL-REF');
    $officer = brOfficer();

    $this->actingAs($officer)->get(route('reconciliation.index'))
        ->assertOk()->assertSee('Bilal Recon')->assertSee('BILAL-REF')->assertSee('$19.00');

    $this->actingAs($officer)->get(route('reconciliation.index', ['tab' => 'due', 'due_until' => now()->addMonths(3)->toDateString()]))
        ->assertOk()->assertSee('Bilal Recon')->assertSee('Record Payment');
});

it('lets an admin assign and remove the office admin role, with an audit trail', function () {
    Role::findOrCreate('office-admin');
    $admin = brAdmin();
    $staff = brUser(['name' => 'Sana Office']);

    $this->actingAs($admin)->post(route('admin.office-admins.assign'), ['account' => $staff->username])->assertSessionHas('success');
    expect($staff->fresh()->hasRole('office-admin'))->toBeTrue();
    $this->actingAs($admin)->get(route('admin.office-admins.index'))->assertOk()->assertSee('Sana Office');

    $this->actingAs($staff->fresh())->get(route('reconciliation.index'))->assertOk();

    $this->actingAs($admin)->delete(route('admin.office-admins.remove', $staff))->assertSessionHas('success');
    expect($staff->fresh()->hasRole('office-admin'))->toBeFalse();
    $this->actingAs($staff->fresh())->get(route('reconciliation.index'))->assertForbidden();

    expect(InstalmentPaymentAudit::whereIn('action', ['office_admin_assigned', 'office_admin_removed'])->pluck('actor_id')->unique()->all())->toBe([$admin->id]);
});

it('keeps the payment audit append-only', function () {
    $inst = brSubmitted(brMember());
    brSvc()->verifySubmission($inst, brOfficer(), brPay());
    $log = InstalmentPaymentAudit::first();

    expect(fn () => $log->update(['description' => 'x']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
});
