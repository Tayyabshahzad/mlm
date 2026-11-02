<?php

use App\Models\ReferralLink;
use App\Models\Setting;
use App\Models\User;
use App\Services\AccountManagementService;
use App\Services\ROICommissionService;
use Illuminate\Support\Facades\DB;

function psUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $user = User::factory()->create(array_merge([
        'username'                       => 'psuser' . $n . '_' . uniqid(),
        'transaction_id'                 => 'TEST-' . uniqid(),
        'phone_number'                   => '0345' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login'                      => true,
        'blocked'                        => false,
        'freez_wallet'                   => false,
        'account_type'                   => 'standard_investment',
        'user_plan'                      => 'standard',
        'roi_eligible_investment_amount' => 100,
    ], $attrs));
    ReferralLink::create(['user_id' => $user->id, 'link' => $user->username]);

    return $user;
}

function psTree(User $anc, User $desc, int $level, string $type = 'standard'): void
{
    DB::table('referral_trees')->insert(['ancestor_id' => $anc->id, 'descendant_id' => $desc->id, 'level' => $level, 'tree_type' => $type]);
}

function psWallet(User $to, string $type, float $amount, array $extra = []): int
{
    return DB::table('wallets')->insertGetId(array_merge([
        'user_id' => $to->id, 'wallet_type' => $type, 'balance' => $amount, 'total_amount' => $amount,
        'direct_balance' => 0, 'indirect_balance' => 0, 'commission_type' => $type, 'level' => '-',
        'created_at' => now(), 'updated_at' => now(),
    ], $extra));
}

/** an active member: has an investment and an older ROI entry */
function psActive(User $anc, int $level, array $attrs = []): User
{
    $u = psUser($attrs);
    psTree($anc, $u, $level);
    psWallet($u, 'roi', 1, ['created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()]);

    return $u;
}

function psService(): ROICommissionService
{
    return new ROICommissionService(new AccountManagementService());
}

function psPaid(User $u): float
{
    return round((float) DB::table('wallets')->where('user_id', $u->id)->where('wallet_type', 'profit_share')->sum('total_amount'), 2);
}

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBeIn(['oracle_18', 'oracle_19']);
    Setting::query()->insert(['standard_profit_l1' => 3.5, 'standard_profit_l2' => 3.0, 'vip_profit_l1' => 1.75, 'vip_profit_l2' => 1.5, 'created_at' => now(), 'updated_at' => now()]);
});

it('pays only the standard-tree upline, once per ROI entry, capped to the ROI amount', function () {
    $upline = psUser();
    $saver  = psUser();                     // saving-tree upline of the same member
    $roiUser = psActive($upline, 1);
    psTree($saver, $roiUser, 1, 'saving');
    for ($i = 0; $i < 9; $i++) psActive($upline, 1);   // 10 active at L1
    for ($i = 0; $i < 12; $i++) psActive($saver, 1);   // saver meets L1 target, but is only the SAVING upline of $roiUser

    psWallet($roiUser, 'roi', 10.00);
    psService()->generateCommissions($roiUser, 10.00);

    expect(psPaid($upline))->toBe(0.35)
        ->and(psPaid($saver))->toBe(0.0);

    // same ROI entry again (e.g. commissions:process-all) → nothing new
    psService()->generateCommissions($roiUser, 10.00);
    expect(psPaid($upline))->toBe(0.35);

    // next ROI, caller passes an inflated multi-day amount → capped to the ROI entry
    psWallet($roiUser, 'roi', 10.00);
    psService()->generateCommissions($roiUser, 500.00);
    expect(psPaid($upline))->toBe(0.70);
});

it('does not pay when the level target is not met by ACTIVE standard members', function () {
    $upline = psUser();
    $roiUser = psActive($upline, 1);
    for ($i = 0; $i < 5; $i++) psActive($upline, 1);
    for ($i = 0; $i < 3; $i++) psTree($upline, psUser(['roi_eligible_investment_amount' => 0]), 1); // no investment
    psTree($upline, psUser(['account_type' => 'saving']), 1);                                     // saving plan
    for ($i = 0; $i < 6; $i++) psActive($upline, 2);                                               // other level never counts

    psWallet($roiUser, 'roi', 10.00);
    psService()->generateCommissions($roiUser, 10.00);

    expect(psPaid($upline))->toBe(0.0);
});

it('reverses overpaid profit share from the profit share wallet, then online, and never twice', function () {
    $upline = psUser(['name' => 'Over Paid']);
    $roiUser = psActive($upline, 1);           // only 1 active member → L1 target (10) not met

    psWallet($roiUser, 'roi', 100.00);
    psWallet($upline, 'profit_share', 5.00, ['balance' => 3.00, 'wallet_from' => $roiUser->id, 'level' => '1', 'percentage' => 5]); // $2 already transferred
    psWallet($upline, 'online', 1.00, ['commission_type' => 'online', 'level' => 'online']);

    // dry run changes nothing
    $this->artisan('profit-share:reverse-overpayments')->assertSuccessful();
    expect((float) DB::table('wallets')->where('user_id', $upline->id)->where('wallet_type', 'profit_share')->sum('balance'))->toBe(3.0);

    // apply without --allow-negative: $3 from profit share, $1 from online, $1 left
    $this->artisan('profit-share:reverse-overpayments', ['--apply' => true, '--force' => true])->assertSuccessful();
    $bal = fn ($t) => round((float) DB::table('wallets')->where('user_id', $upline->id)->where('wallet_type', $t)->sum('balance'), 2);
    expect($bal('profit_share'))->toBe(0.0)
        ->and($bal('online'))->toBe(0.0)
        ->and(psPaid($upline))->toBe(5.0); // original amount untouched

    // remainder with --allow-negative → online goes to -1
    $this->artisan('profit-share:reverse-overpayments', ['--apply' => true, '--force' => true, '--allow-negative' => true])->assertSuccessful();
    expect($bal('online'))->toBe(-1.0)
        ->and((float) DB::table('wallets')->where('user_id', $upline->id)->where('wallet_type', 'profit_share_reversal')->sum('total_amount'))->toBe(-5.0)
        ->and(DB::table('transaction_logs')->where('user_id', $upline->id)->where('to_wallet_type', 'profit_share_reversal')->count())->toBe(3);

    // running again takes nothing more
    $this->artisan('profit-share:reverse-overpayments', ['--apply' => true, '--force' => true, '--allow-negative' => true])->assertSuccessful();
    expect($bal('online'))->toBe(-1.0);

    // the member sees why the online wallet is negative
    $this->actingAs($upline)->get(route('wallets.online'))->assertOk()
        ->assertSee('Your Online Wallet is negative: -$1.00', false)
        ->assertSee('$5.00', false)
        ->assertSee('profit share that was paid to you by mistake', false);
});

it('shows no reversal notice when the online wallet is not negative', function () {
    $user = psUser();
    psWallet($user, 'online', 10.00, ['commission_type' => 'online', 'level' => 'online']);

    $this->actingAs($user)->get(route('wallets.online'))->assertOk()
        ->assertDontSee('Your Online Wallet is negative');
});

it('pays the rightful upline what was missed, once, without it being flagged as overpaid', function () {
    $rightful = psUser();
    psWallet($rightful, 'roi', 1, ['created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()]); // has an investment
    $wrong    = psUser();                        // saving-tree upline got it instead
    $roiUser  = psActive($rightful, 1);
    for ($i = 0; $i < 9; $i++) psActive($rightful, 1); // 10 active → L1 target met
    psTree($wrong, $roiUser, 1, 'saving');

    psWallet($roiUser, 'roi', 100.00);
    psWallet($wrong, 'profit_share', 3.50, ['wallet_from' => $roiUser->id, 'level' => '1', 'percentage' => 3.5]);

    $this->artisan('profit-share:pay-missed')->assertSuccessful();
    expect(psPaid($rightful))->toBe(0.0);                      // dry run

    $this->artisan('profit-share:pay-missed', ['--apply' => true, '--force' => true])->assertSuccessful();
    expect(psPaid($rightful))->toBe(3.5)
        ->and((float) DB::table('wallets')->where('user_id', $rightful->id)->where('wallet_type', 'profit_share')->sum('balance'))->toBe(3.5);

    $this->artisan('profit-share:pay-missed', ['--apply' => true, '--force' => true])->assertSuccessful();
    expect(psPaid($rightful))->toBe(3.5);                      // never twice

    // the credit is not treated as an overpayment; the wrong upline is
    $result = app(\App\Services\ProfitShareAuditService::class)->run();
    expect((float) ($result['users'][$rightful->id]['over'] ?? 0))->toBe(0.0)
        ->and($result['users'][$wrong->id]['over'])->toBe(3.5);
});

it('skips excluded members', function () {
    $upline = psUser();
    $roiUser = psActive($upline, 1);
    psWallet($roiUser, 'roi', 100.00);
    psWallet($upline, 'profit_share', 5.00, ['wallet_from' => $roiUser->id, 'level' => '1', 'percentage' => 5]);

    $this->artisan('profit-share:reverse-overpayments', ['--apply' => true, '--force' => true, '--exclude' => [$upline->username]])->assertSuccessful();

    expect((float) DB::table('wallets')->where('user_id', $upline->id)->where('wallet_type', 'profit_share')->sum('balance'))->toBe(5.0);
});
