<?php

use App\Models\ReferralLink;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function mtUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $u = User::factory()->create(array_merge([
        'username' => 'mtuser' . $n . '_' . uniqid(), 'transaction_id' => 'T-' . uniqid(),
        'phone_number' => '0347' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login' => true, 'blocked' => false,
    ], $attrs));
    ReferralLink::create(['user_id' => $u->id, 'link' => $u->username]);
    DB::table('wallets')->insert(['user_id' => $u->id, 'wallet_type' => 'online', 'balance' => 100, 'total_amount' => 0,
        'direct_balance' => 0, 'indirect_balance' => 0, 'commission_type' => 'online', 'level' => 'online', 'created_at' => now(), 'updated_at' => now()]);

    return $u;
}

function mtOnline(User $u): float
{
    return round((float) DB::table('wallets')->where('user_id', $u->id)->where('wallet_type', 'online')->sum('balance'), 2);
}

function mtSend(User $from, User $to, float $amount = 10)
{
    return test()->actingAs($from)->post(route('withdrawals.member.transfer'), [
        'amount' => $amount, 'description' => 'test', 'member_account' => $to->username, 'wallet_type' => 'member_transfer',
    ]);
}

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBeIn(['oracle_18', 'oracle_19', 'oracle_20']);
    Setting::query()->insert(['min_member_transfer' => 7, 'created_at' => now(), 'updated_at' => now()]);

    //          top
    //         /    \
    //        A      B        (A and B are different teams: left / right)
    //        |      |
    //        A1     B1
    $this->top = mtUser();
    $this->a   = mtUser(['sponsor_id' => $this->top->id]);
    $this->b   = mtUser(['sponsor_id' => $this->top->id]);
    $this->a1  = mtUser(['sponsor_id' => $this->a->id]);
    $this->b1  = mtUser(['sponsor_id' => $this->b->id]);
    $rows = [];
    foreach ([[$this->top, $this->a, 1], [$this->top, $this->b, 1], [$this->top, $this->a1, 2], [$this->top, $this->b1, 2],
              [$this->a, $this->a1, 1], [$this->b, $this->b1, 1]] as [$anc, $desc, $lvl]) {
        $rows[] = ['ancestor_id' => $anc->id, 'descendant_id' => $desc->id, 'level' => $lvl, 'tree_type' => 'standard'];
    }
    DB::table('referral_trees')->insert($rows);
});

it('allows transfer to your downline and to your upline at any depth', function () {
    mtSend($this->a, $this->a1)->assertSessionHas('success');      // downline
    mtSend($this->a1, $this->top)->assertSessionHas('success');    // upline, 2 levels up

    expect(mtOnline($this->a))->toBe(90.0)
        ->and(mtOnline($this->a1))->toBe(100.0)
        ->and(mtOnline($this->top))->toBe(110.0);
});

it('blocks transfer to other teams (left / right, cousins)', function () {
    mtSend($this->a, $this->b)->assertSessionHas('error');         // sibling team
    mtSend($this->a1, $this->b1)->assertSessionHas('error');       // cousin
    mtSend($this->b1, $this->a)->assertSessionHas('error');

    foreach ([$this->a, $this->b, $this->a1, $this->b1] as $u) expect(mtOnline($u))->toBe(100.0);
});

it('allows the line through the saving tree too', function () {
    $saver = mtUser();
    DB::table('referral_trees')->insert(['ancestor_id' => $this->b->id, 'descendant_id' => $saver->id, 'level' => 1, 'tree_type' => 'saving']);

    mtSend($saver, $this->b)->assertSessionHas('success');
    mtSend($saver, $this->a)->assertSessionHas('error');
});

it('blocks transfer to yourself', function () {
    mtSend($this->a, $this->a)->assertSessionHas('error');
    expect(mtOnline($this->a))->toBe(100.0);
});
