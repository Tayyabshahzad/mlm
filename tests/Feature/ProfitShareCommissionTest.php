<?php

use App\Models\ReferralTree;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ROITransaction;
use App\Services\AccountManagementService;
use App\Services\ROICommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Create a minimal active user with investment.
 */
function makeUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'blocked'                      => false,
        'can_login'                    => true,
        'freez_wallet'                 => false,
        'roi_eligible_investment_amount' => 500,
        'roi_status'                   => 'active',
        'user_plan'                    => 'standard',
    ], $attributes));
}

/**
 * Register $descendant as being at $level under $ancestor in referral_trees.
 */
function linkTree(User $ancestor, User $descendant, int $level): void
{
    ReferralTree::create([
        'ancestor_id'   => $ancestor->id,
        'descendant_id' => $descendant->id,
        'level'         => $level,
    ]);
}

/**
 * Create a Setting row with explicit standard & VIP profit-share percentages
 * for all 7 levels.
 */
function makeSetting(array $overrides = []): Setting
{
    return Setting::create(array_merge([
        // Standard plan rates
        'standard_profit_l1' => 7.0,
        'standard_profit_l2' => 6.0,
        'standard_profit_l3' => 5.0,
        'standard_profit_l4' => 4.0,
        'standard_profit_l5' => 3.0,
        'standard_profit_l6' => 2.0,
        'standard_profit_l7' => 1.0,
        // VIP plan rates (deliberately different)
        'vip_profit_l1' => 3.5,
        'vip_profit_l2' => 3.0,
        'vip_profit_l3' => 2.5,
        'vip_profit_l4' => 2.0,
        'vip_profit_l5' => 1.5,
        'vip_profit_l6' => 1.0,
        'vip_profit_l7' => 0.5,
    ], $overrides));
}

/**
 * Build the service under test with a real AccountManagementService.
 */
function makeService(): ROICommissionService
{
    return new ROICommissionService(new AccountManagementService());
}

// ─── Tests ────────────────────────────────────────────────────────────────────

it('pays standard plan ancestor at level 1 using standard_profit_l1 rate', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'standard']);
    $roiUser  = makeUser();

    // Place roiUser at level 1 under ancestor — meet minimum 10 users
    for ($i = 0; $i < 10; $i++) {
        $filler = makeUser();
        linkTree($ancestor, $filler, 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->where('level', 1)
        ->first();

    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->percentage)->toBe(7.0)
        ->and((float) $wallet->total_amount)->toBe(7.0); // 7% of 100
});

it('pays vip plan ancestor at level 1 using vip_profit_l1 rate (3.5%), NOT standard 7%', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'vip']);
    $roiUser  = makeUser();

    for ($i = 0; $i < 10; $i++) {
        $filler = makeUser();
        linkTree($ancestor, $filler, 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->where('level', 1)
        ->first();

    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->percentage)->toBe(3.5)
        ->and((float) $wallet->total_amount)->toBe(3.5); // 3.5% of 100
});

it('standard and vip ancestors at same level receive DIFFERENT amounts from same roi user', function () {
    makeSetting();

    $standardAncestor = makeUser(['user_plan' => 'standard']);
    $vipAncestor      = makeUser(['user_plan' => 'vip']);
    $roiUser          = makeUser();

    // roiUser is at level 1 under both ancestors
    for ($i = 0; $i < 10; $i++) {
        linkTree($standardAncestor, makeUser(), 1);
        linkTree($vipAncestor, makeUser(), 1);
    }
    linkTree($standardAncestor, $roiUser, 1);
    linkTree($vipAncestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 200.00);

    $standardWallet = Wallet::where('user_id', $standardAncestor->id)
        ->where('wallet_type', 'profit_share')->where('level', 1)->first();

    $vipWallet = Wallet::where('user_id', $vipAncestor->id)
        ->where('wallet_type', 'profit_share')->where('level', 1)->first();

    expect($standardWallet)->not->toBeNull()
        ->and($vipWallet)->not->toBeNull()
        ->and((float) $standardWallet->total_amount)->toBe(14.0)  // 7% of 200
        ->and((float) $vipWallet->total_amount)->toBe(7.0);       // 3.5% of 200
});

it('pays correct rates across all 7 levels for standard plan', function () {
    makeSetting();

    $roiUser = makeUser();

    $expectedRates = [
        1 => ['rate' => 7.0, 'min_users' => 10],
        2 => ['rate' => 6.0, 'min_users' => 50],
        3 => ['rate' => 5.0, 'min_users' => 150],
        4 => ['rate' => 4.0, 'min_users' => 400],
        5 => ['rate' => 3.0, 'min_users' => 1000],
        6 => ['rate' => 2.0, 'min_users' => 2000],
        7 => ['rate' => 1.0, 'min_users' => 4000],
    ];

    $ancestors = [];
    foreach ($expectedRates as $level => $config) {
        $ancestor = makeUser(['user_plan' => 'standard']);
        $ancestors[$level] = $ancestor;

        // Fill minimum required users at this exact level
        for ($i = 0; $i < $config['min_users']; $i++) {
            linkTree($ancestor, makeUser(), $level);
        }
        // Place roiUser at this level under ancestor
        linkTree($ancestor, $roiUser, $level);
    }

    makeService()->generateCommissions($roiUser, 100.00);

    foreach ($expectedRates as $level => $config) {
        $wallet = Wallet::where('user_id', $ancestors[$level]->id)
            ->where('wallet_type', 'profit_share')
            ->where('level', $level)
            ->first();

        expect($wallet)->not->toBeNull("Level {$level} wallet should exist")
            ->and((float) $wallet->percentage)->toBe($config['rate'], "Level {$level} rate mismatch")
            ->and((float) $wallet->total_amount)->toBe(round(100 * $config['rate'] / 100, 2));
    }
});

it('pays correct rates across all 7 levels for vip plan', function () {
    makeSetting();

    $roiUser = makeUser();

    $expectedRates = [
        1 => ['rate' => 3.5,  'min_users' => 10],
        2 => ['rate' => 3.0,  'min_users' => 50],
        3 => ['rate' => 2.5,  'min_users' => 150],
        4 => ['rate' => 2.0,  'min_users' => 400],
        5 => ['rate' => 1.5,  'min_users' => 1000],
        6 => ['rate' => 1.0,  'min_users' => 2000],
        7 => ['rate' => 0.5,  'min_users' => 4000],
    ];

    $ancestors = [];
    foreach ($expectedRates as $level => $config) {
        $ancestor = makeUser(['user_plan' => 'vip']);
        $ancestors[$level] = $ancestor;

        for ($i = 0; $i < $config['min_users']; $i++) {
            linkTree($ancestor, makeUser(), $level);
        }
        linkTree($ancestor, $roiUser, $level);
    }

    makeService()->generateCommissions($roiUser, 100.00);

    foreach ($expectedRates as $level => $config) {
        $wallet = Wallet::where('user_id', $ancestors[$level]->id)
            ->where('wallet_type', 'profit_share')
            ->where('level', $level)
            ->first();

        expect($wallet)->not->toBeNull("Level {$level} vip wallet should exist")
            ->and((float) $wallet->percentage)->toBe($config['rate'], "VIP level {$level} rate mismatch")
            ->and((float) $wallet->total_amount)->toBe(round(100 * $config['rate'] / 100, 2));
    }
});

it('does NOT pay commission when ancestor has fewer users than required at the level', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'standard']);
    $roiUser  = makeUser();

    // Only 9 users at level 1 — minimum is 10 — should NOT trigger payment
    for ($i = 0; $i < 9; $i++) {
        linkTree($ancestor, makeUser(), 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->first();

    expect($wallet)->toBeNull();
});

it('does NOT pay commission to a blocked ancestor', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'standard', 'blocked' => true]);
    $roiUser  = makeUser();

    for ($i = 0; $i < 10; $i++) {
        linkTree($ancestor, makeUser(), 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->first();

    expect($wallet)->toBeNull();
});

it('does NOT pay commission to a frozen ancestor', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'standard', 'freez_wallet' => true]);
    $roiUser  = makeUser();

    for ($i = 0; $i < 10; $i++) {
        linkTree($ancestor, makeUser(), 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->first();

    expect($wallet)->toBeNull();
});

it('does NOT pay commission to an ancestor with no investment', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'standard', 'roi_eligible_investment_amount' => 0]);
    $roiUser  = makeUser();

    for ($i = 0; $i < 10; $i++) {
        linkTree($ancestor, makeUser(), 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->first();

    expect($wallet)->toBeNull();
});

it('creates both a Wallet record and an ROITransaction record for each paid commission', function () {
    makeSetting();

    $ancestor = makeUser(['user_plan' => 'standard']);
    $roiUser  = makeUser();

    for ($i = 0; $i < 10; $i++) {
        linkTree($ancestor, makeUser(), 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    expect(
        Wallet::where('user_id', $ancestor->id)->where('wallet_type', 'profit_share')->count()
    )->toBe(1);

    expect(
        ROITransaction::where('user_id', $ancestor->id)
            ->where('description', 'like', 'Level 1 commission%')
            ->count()
    )->toBe(1);
});

it('falls back to default percentage when no Setting record exists', function () {
    // No Setting created — service should fall back to hardcoded defaults
    // Standard fallback for level 1 = 7.0 (COMMISSION_LEVELS[1])
    $ancestor = makeUser(['user_plan' => 'standard']);
    $roiUser  = makeUser();

    for ($i = 0; $i < 10; $i++) {
        linkTree($ancestor, makeUser(), 1);
    }
    linkTree($ancestor, $roiUser, 1);

    makeService()->generateCommissions($roiUser, 100.00);

    $wallet = Wallet::where('user_id', $ancestor->id)
        ->where('wallet_type', 'profit_share')
        ->where('level', 1)
        ->first();

    // Should still get paid with the hardcoded default
    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->percentage)->toBe(7.0);
});
