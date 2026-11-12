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
