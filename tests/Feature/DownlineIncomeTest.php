<?php

use App\Models\ReferralLink;
use App\Models\Setting;
use App\Models\User;
use App\Services\DownlineIncomeService;
use Illuminate\Support\Facades\DB;

function diUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $user = User::factory()->create(array_merge([
        'username'       => 'diuser' . $n . '_' . uniqid(),
        'transaction_id' => 'TEST-' . uniqid(),
        'phone_number'   => '0344' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login'      => true,
        'blocked'        => false,
    ], $attrs));
    ReferralLink::create(['user_id' => $user->id, 'link' => $user->username]);

    return $user;
}

function diWallet(User $to, ?User $from, string $type, float $amount, $at, array $extra = []): void
{
    DB::table('wallets')->insert(array_merge([
        'user_id' => $to->id, 'wallet_from' => $from?->id, 'wallet_type' => $type,
        'balance' => $amount, 'total_amount' => $amount, 'direct_balance' => 0, 'indirect_balance' => 0,
        'commission_type' => $type === 'profit_share' ? 'profit_share' : 'direct', 'level' => '1',
        'created_at' => $at, 'updated_at' => $at,
    ], $extra));
}

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBe('oracle_18');
    Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
});

it('includes commission from new AND existing downline earned this month, net of adjustments', function () {
    $me       = diUser();
    $old      = diUser(['name' => 'Old Member', 'created_at' => now()->subMonths(3)]);
    $new      = diUser(['name' => 'New Member', 'created_at' => now()->startOfMonth()->addHours(2)]);
    $deep     = diUser(['name' => 'Deep Member', 'created_at' => now()->subYear()]);
    $stranger = diUser();
    DB::table('referral_trees')->insert([
        ['ancestor_id' => $me->id, 'descendant_id' => $old->id,  'level' => 1, 'tree_type' => 'standard'],
        ['ancestor_id' => $me->id, 'descendant_id' => $new->id,  'level' => 1, 'tree_type' => 'saving'],
        ['ancestor_id' => $me->id, 'descendant_id' => $deep->id, 'level' => 2, 'tree_type' => 'standard'],
    ]);

    $thisMonth = now()->startOfMonth()->addDay();
    diWallet($me, $old,  'direct_indirect', 10.00, $thisMonth);                                   // existing, L1
    diWallet($me, $old,  'profit_share',     2.50, $thisMonth);                                   // existing, profit share
    diWallet($me, $new,  'direct_indirect',  5.00, $thisMonth, ['source_type' => 'saving_instalment']); // new, L1
    diWallet($me, $deep, 'direct_indirect',  1.25, $thisMonth, ['commission_type' => 'indirect', 'level' => '2']); // existing, L2
    diWallet($me, $old,  'direct_indirect', 99.00, now()->subMonth()->startOfMonth());           // last month — excluded
    diWallet($stranger, $old, 'direct_indirect', 50.00, $thisMonth);                             // someone else's income — excluded
    diWallet($me, $old, 'roi', 7.00, $thisMonth);                                                  // own ROI — not downline commission
    diWallet($me, null, 'direct_indirect', 0.75, $thisMonth, ['commission_type' => 'balance_correction', 'balance' => -0.75, 'transaction_type' => 'debit']);
    diWallet($me, null, 'profit_share_reversal', -0.50, $thisMonth, ['commission_type' => 'profit_share_reversal']);

    $s = app(DownlineIncomeService::class)->summary($me, now()->startOfMonth(), now());

    expect($s['gross'])->toBe(18.75)
        ->and($s['direct_indirect'])->toBe(16.25)
        ->and($s['profit_share'])->toBe(2.5)
        ->and($s['adjustments'])->toBe(1.25)
        ->and($s['net'])->toBe(17.5)
        ->and($s['from_new'])->toBe(5.0)
        ->and($s['from_existing'])->toBe(13.75)
        ->and($s['members'])->toBe(3)
        ->and($s['new_members'])->toBe(1);

    expect($s['by_level']->all())->toBe([
        ['level' => 1, 'amount' => 17.5, 'members' => 2],
        ['level' => 2, 'amount' => 1.25, 'members' => 1],
    ]);
    expect($s['top_members']->pluck('name')->all())->toBe(['Old Member', 'New Member', 'Deep Member'])
        ->and($s['top_members']->firstWhere('name', 'New Member')['is_new'])->toBeTrue()
        ->and($s['top_members']->firstWhere('name', 'Old Member')['is_new'])->toBeFalse();

    // Shown on Team Performance
    $this->actingAs($me)->get(route('team.performance'))->assertOk()
        ->assertSee('Current Month Income')
        ->assertSee('$17.50')
        ->assertSee('Old Member')->assertSee('Existing')
        ->assertSee('New Member')->assertSee('New');
});

it('shows the selected-period income when a date range is chosen', function () {
    $me  = diUser();
    $old = diUser(['created_at' => now()->subMonths(5)]);
    DB::table('referral_trees')->insert(['ancestor_id' => $me->id, 'descendant_id' => $old->id, 'level' => 1, 'tree_type' => 'standard']);
    diWallet($me, $old, 'direct_indirect', 4.00, now()->subMonths(2));

    $this->actingAs($me)->get(route('team.performance', ['from' => now()->subMonths(3)->toDateString(), 'to' => now()->toDateString()]))
        ->assertOk()->assertSee('Selected period')->assertSee('$4.00');
});

it('lets an admin see another member\'s monthly income', function () {
    \Spatie\Permission\Models\Role::findOrCreate('admin');
    $admin = diUser();
    $admin->assignRole('admin');
    $me  = diUser(['name' => 'Target Member']);
    $old = diUser(['created_at' => now()->subMonths(2)]);
    DB::table('referral_trees')->insert(['ancestor_id' => $me->id, 'descendant_id' => $old->id, 'level' => 1, 'tree_type' => 'standard']);
    diWallet($me, $old, 'direct_indirect', 3.33, now()->startOfMonth()->addDay());

    $this->actingAs($admin)->get(route('team.performance', ['user_id' => $me->id]))
        ->assertOk()->assertSee('Target Member')->assertSee('$3.33');
});

it('shows current month saving commission from new AND existing downline on the Savings Direct/Indirect page', function () {
    $me  = diUser(['account_type' => 'saving', 'saving_registration_completed' => true]);
    $old = diUser(['name' => 'Old Saver', 'created_at' => now()->subMonths(4)]);
    $new = diUser(['name' => 'New Saver', 'created_at' => now()->startOfMonth()->addHours(3)]);
    DB::table('referral_trees')->insert([
        ['ancestor_id' => $me->id, 'descendant_id' => $old->id, 'level' => 1, 'tree_type' => 'saving'],
        ['ancestor_id' => $me->id, 'descendant_id' => $new->id, 'level' => 2, 'tree_type' => 'saving'],
    ]);

    $t = now()->startOfMonth()->addDay();
    $saving = ['source_type' => 'saving_instalment'];
    diWallet($me, $old, 'direct_indirect', 1.33, $t, $saving + ['commission_type' => 'direct']);                    // existing, direct
    diWallet($me, $new, 'direct_indirect', 0.38, $t, $saving + ['commission_type' => 'indirect', 'level' => '2']);  // new, indirect
    diWallet($me, $old, 'direct_indirect', 9.00, now()->subMonth()->startOfMonth(), $saving);                     // last month
    diWallet($me, $old, 'direct_indirect', 5.00, $t, ['source_type' => 'investment']);                            // standard plan — not this page
    diWallet($me, $old, 'profit_share', 2.00, $t);                                                                  // not a saving commission
    diWallet($me, null, 'direct_indirect', 0.21, $t, $saving + ['commission_type' => 'balance_correction', 'balance' => -0.21, 'transaction_type' => 'debit']);

    $s = app(DownlineIncomeService::class)->summary($me, now()->startOfMonth(), now(), ['direct_indirect'], 'saving_instalment');
    expect($s['gross'])->toBe(1.71)
        ->and($s['direct'])->toBe(1.33)
        ->and($s['indirect'])->toBe(0.38)
        ->and($s['adjustments'])->toBe(0.21)
        ->and($s['net'])->toBe(1.5)
        ->and($s['from_new'])->toBe(0.38)
        ->and($s['from_existing'])->toBe(1.33);

    $this->actingAs($me)->get(route('wallets.saving.direct.indirect'))->assertOk()
        ->assertSee('Current Month Income')
        ->assertSee('$1.50')
        ->assertSee('Old Saver')->assertSee('Existing')
        ->assertSee('New Saver')->assertSee('New');
});
