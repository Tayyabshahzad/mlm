<?php

use App\Models\ReferralLink;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

function viUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $user = User::factory()->create(array_merge([
        'username'       => 'viuser' . $n . '_' . uniqid(),
        'transaction_id' => 'TEST-' . uniqid(),
        'phone_number'   => '0333' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login'      => true,
        'blocked'        => false,
    ], $attrs));
    ReferralLink::create(['user_id' => $user->id, 'link' => $user->username]);

    return $user;
}

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBe('oracle_18');
    Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);
});

it('every route name used in a Blade view exists', function () {
    $missing = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $file) {
        if (!str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        preg_match_all("/route\(\s*'([A-Za-z0-9._-]+)'/", file_get_contents($file->getPathname()), $m);
        foreach (array_unique($m[1]) as $name) {
            if (!Route::has($name)) {
                $missing[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $file->getPathname()) . " → {$name}";
            }
        }
    }

    // genealogy.team.member.status.update is referenced only behind a Route::has() guard
    $missing = array_values(array_filter($missing, fn ($m) => !str_ends_with($m, 'genealogy.team.member.status.update')));

    expect($missing)->toBe([]);
});

it('no view uses the escaped @{{ … }} echo by mistake', function () {
    $hits = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $file) {
        if (str_ends_with($file->getFilename(), '.blade.php')
            && preg_match('/@\{\{(?!--)/', file_get_contents($file->getPathname()))) {
            $hits[] = $file->getFilename();
        }
    }

    expect($hits)->toBe([]);
});

it('renders Saving Enrollments with enrolled members listed', function () {
    Role::findOrCreate('admin');
    $admin = viUser();
    $admin->assignRole('admin');
    $enrolled = viUser(['name' => 'Enrolled Person', 'saving_enrolled' => true, 'saving_enrollment_activated' => false]);
    // An already-activated enrollment (activated_at is stored as a plain string — no model cast)
    viUser(['name' => 'Activated Person', 'saving_enrolled' => true, 'saving_enrollment_activated' => true,
        'saving_enrollment_activated_at' => '2026-10-20 14:30:00', 'saving_plan_start_date' => '2026-10-01']);

    $this->actingAs($admin)->get(route('admin.saving.enrollments'))
        ->assertOk()
        ->assertSee('Activated Person')
        ->assertSee('20 Oct 2026 14:30')
        ->assertSee('Enrolled Person')
        ->assertSee(route('admin.saving.show', $enrolled), false)
        ->assertSee('@ ' . $enrolled->username)
        ->assertDontSee('{{ $user->username }}', false);
});

it('renders the team members page instead of crashing', function () {
    $sponsor = viUser();
    $member  = viUser(['name' => 'Direct Member', 'can_login' => false]);
    DB::table('referral_trees')->insert(['ancestor_id' => $sponsor->id, 'descendant_id' => $member->id, 'level' => 1, 'tree_type' => 'standard']);

    $this->actingAs($sponsor)->get(route('genealogy.team.members'))->assertOk()->assertSee('Direct Member');
});
