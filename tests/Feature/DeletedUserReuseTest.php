<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function duUser(array $attrs = []): User
{
    return User::factory()->create(array_merge([
        'username' => 'du_' . uniqid(), 'transaction_id' => 'T-' . uniqid(), 'phone_number' => '0300' . rand(1000000, 9999999),
    ], $attrs));
}

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBeIn(['oracle_18', 'oracle_19', 'oracle_20', 'oracle_21']);
});

it('frees the CNIC, email and username when an account is deleted, so the person can register again', function () {
    $old = duUser(['name' => 'Romana bibi', 'username' => 'romana-bibi874', 'email' => 'romana@example.com', 'cnic' => '12104-8568187-4']);
    $old->delete();

    $trashed = User::withTrashed()->find($old->id);
    expect($trashed->cnic)->toBe('del' . $old->id . '-12104-8568187-4')   // still readable
        ->and($trashed->username)->toStartWith('deleted_')
        ->and($trashed->email)->toEndWith('@deleted.invalid');

    // same person, same CNIC / username / email → no "Duplicate entry"
    $new = duUser(['name' => 'Romana bibi', 'username' => 'romana-bibi874', 'email' => 'romana@example.com', 'cnic' => '12104-8568187-4']);
    expect($new->exists)->toBeTrue();
});

it('the generated username skips names still held by deleted accounts', function () {
    $held = duUser(['username' => 'romana-bibi874']);
    DB::table('users')->where('id', $held->id)->update(['deleted_at' => now()]);   // an old delete that kept its username

    $method = new ReflectionMethod(RegisteredUserController::class, 'generateUsername');
    $name = $method->invoke(app(RegisteredUserController::class), 'Romana Bibi', '12104-8568187-4');

    expect($name)->toBe('romana-bibi874-1');
});

it('frees the CNIC of accounts that were deleted before this fix (migration)', function () {
    $u = duUser(['cnic' => '35202-1234567-1']);
    DB::table('users')->where('id', $u->id)->update(['deleted_at' => now()]);  // deleted the old way

    $migration = require database_path('migrations/2026_11_12_000001_free_cnic_of_deleted_users.php');
    $migration->up();

    expect(DB::table('users')->where('id', $u->id)->value('cnic'))->toBe('del' . $u->id . '-35202-1234567-1');
    $migration->up();                                                           // running again changes nothing
    expect(DB::table('users')->where('id', $u->id)->value('cnic'))->toBe('del' . $u->id . '-35202-1234567-1');
});

function duAdmin(): User
{
    \Spatie\Permission\Models\Role::findOrCreate('admin');
    \App\Models\Setting::query()->firstOrCreate([], ['created_at' => now()]);
    $a = duUser(['can_login' => true, 'blocked' => false]);
    $a->assignRole('admin');

    return $a;
}

function duWallet(User $to, ?User $from = null): void
{
    DB::table('wallets')->insert(['user_id' => $to->id, 'wallet_from' => $from?->id, 'wallet_type' => 'online', 'balance' => 5, 'total_amount' => 5,
        'direct_balance' => 0, 'indirect_balance' => 0, 'commission_type' => 'online', 'level' => '-', 'created_at' => now(), 'updated_at' => now()]);
}

it('admin delete removes a wrongly created account permanently, so it can be created again with the same details', function () {
    $sponsor = duUser();
    $wrong = duUser(['name' => 'Romana bibi', 'username' => 'romana-bibi874', 'email' => 'romana@example.com', 'cnic' => '12104-8568187-4', 'sponsor_id' => $sponsor->id]);
    \App\Models\ReferralLink::create(['user_id' => $wrong->id, 'link' => 'romana-bibi874']);
    DB::table('referral_trees')->insert(['ancestor_id' => $sponsor->id, 'descendant_id' => $wrong->id, 'level' => 1, 'tree_type' => 'standard']);
    duWallet($wrong);

    $this->actingAs(duAdmin())->post(route('user.delete'), ['delete_id' => $wrong->id])->assertSessionHas('success');

    expect(User::withTrashed()->find($wrong->id))->toBeNull()                       // gone for good
        ->and(DB::table('wallets')->where('user_id', $wrong->id)->count())->toBe(0)
        ->and(DB::table('referral_links')->where('user_id', $wrong->id)->count())->toBe(0)
        ->and(DB::table('referral_trees')->where('descendant_id', $wrong->id)->count())->toBe(0)
        ->and(User::find($sponsor->id))->not->toBeNull();                          // sponsor untouched

    $again = duUser(['username' => 'romana-bibi874', 'email' => 'romana@example.com', 'cnic' => '12104-8568187-4', 'sponsor_id' => $sponsor->id]);
    \App\Models\ReferralLink::create(['user_id' => $again->id, 'link' => 'romana-bibi874']);
    expect($again->exists)->toBeTrue();
});

it('admin delete keeps an account that has a team, but frees its username, CNIC and referral link', function () {
    $leader = duUser(['username' => 'leader1', 'cnic' => '11111-1111111-1']);
    \App\Models\ReferralLink::create(['user_id' => $leader->id, 'link' => 'leader1']);
    $member = duUser(['sponsor_id' => $leader->id]);

    $this->actingAs(duAdmin())->post(route('user.delete'), ['delete_id' => $leader->id])->assertSessionHas('success');

    $kept = User::withTrashed()->find($leader->id);
    expect($kept)->not->toBeNull()->and($kept->trashed())->toBeTrue()
        ->and(User::find($member->id))->not->toBeNull()                            // downline NOT wiped
        ->and(DB::table('referral_links')->where('user_id', $leader->id)->value('link'))->toBe('del' . $leader->id . '-leader1');

    $new = duUser(['username' => 'leader1', 'cnic' => '11111-1111111-1']);
    \App\Models\ReferralLink::create(['user_id' => $new->id, 'link' => 'leader1']);
    expect($new->exists)->toBeTrue();
});

it('keeps an account whose commissions went to other users', function () {
    $up = duUser();
    $u = duUser();
    duWallet($up, $u);   // upline earned from this account
    expect($u->permanentDeleteBlockers())->toContain('gave commission/profit to other users');
});

it('purges old deleted accounts nobody depends on, and keeps the rest', function () {
    $clean = duUser(['cnic' => '22222-2222222-2']);
    $clean->delete();                                                   // old soft delete
    $withTeam = duUser();
    duUser(['sponsor_id' => $withTeam->id]);
    $withTeam->delete();

    // (only these two: the migration test above commits its data because ALTER TABLE ends the test transaction)
    $only = ['--user' => [(string) $clean->id, (string) $withTeam->id]];
    $this->artisan('users:purge-deleted', $only)->assertSuccessful();   // list only
    expect(User::withTrashed()->find($clean->id))->not->toBeNull();

    $this->artisan('users:purge-deleted', $only + ['--apply' => true])->expectsConfirmation('Remove 1 account(s) permanently? This cannot be undone.', 'yes')->assertSuccessful();
    expect(User::withTrashed()->find($clean->id))->toBeNull()
        ->and(User::withTrashed()->find($withTeam->id))->not->toBeNull();
});

it('frees referral links of accounts deleted before this fix (migration)', function () {
    $u = duUser();
    \App\Models\ReferralLink::create(['user_id' => $u->id, 'link' => 'old-link-1']);
    DB::table('users')->where('id', $u->id)->update(['deleted_at' => now()]);

    (require database_path('migrations/2026_11_13_000001_free_referral_links_of_deleted_users.php'))->up();

    expect(DB::table('referral_links')->where('user_id', $u->id)->value('link'))->toBe('del' . $u->id . '-old-link-1');
});
