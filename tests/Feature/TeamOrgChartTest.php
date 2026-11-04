<?php

use App\Models\ReferralLink;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

function ocUser(array $attrs = []): User
{
    static $n = 0;
    $n++;
    $u = User::factory()->create(array_merge([
        'username' => 'ocuser' . $n . '_' . uniqid(), 'transaction_id' => 'T-' . uniqid(),
        'phone_number' => '0346' . str_pad((string) $n, 7, '0', STR_PAD_LEFT),
        'can_login' => true, 'blocked' => false, 'account_type' => 'standard_investment', 'user_plan' => 'standard',
        'roi_eligible_investment_amount' => 100,
    ], $attrs));
    ReferralLink::create(['user_id' => $u->id, 'link' => $u->username]);

    return $u;
}

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBeIn(['oracle_18', 'oracle_19']);
    Setting::query()->insert(['created_at' => now(), 'updated_at' => now()]);

    $this->me    = ocUser(['name' => 'Team Owner']);
    $this->child = ocUser(['name' => 'Child Member', 'sponsor_id' => $this->me->id, 'user_plan' => 'vip', 'email' => 'child@example.com']);
    $this->grand = ocUser(['name' => 'Grand Child', 'sponsor_id' => $this->child->id]);
    $this->other = ocUser(['name' => 'Outsider']);
    DB::table('referral_trees')->insert([
        ['ancestor_id' => $this->me->id, 'descendant_id' => $this->child->id, 'level' => 1, 'tree_type' => 'standard'],
        ['ancestor_id' => $this->me->id, 'descendant_id' => $this->grand->id, 'level' => 2, 'tree_type' => 'standard'],
        ['ancestor_id' => $this->child->id, 'descendant_id' => $this->grand->id, 'level' => 1, 'tree_type' => 'standard'],
    ]);
    DB::table('wallets')->insert(['user_id' => $this->child->id, 'wallet_type' => 'roi', 'balance' => 12.5, 'total_amount' => 12.5,
        'direct_balance' => 0, 'indirect_balance' => 0, 'commission_type' => 'Roi', 'level' => '-', 'created_at' => now(), 'updated_at' => now()]);
});

it('renders the org chart with the whole team', function () {
    $this->actingAs($this->me)->get(route('genealogy.team'))->assertOk()
        ->assertSee('id="ocWrap"', false)
        ->assertSee('Child Member')->assertSee('Grand Child')
        ->assertDontSee('Outsider')
        ->assertDontSee('go.TreeModel', false);
});

it('returns details of a downline member for the side panel, without contact for members', function () {
    $d = $this->actingAs($this->me)->getJson(route('genealogy.team.member', $this->child))->assertOk()->json();

    expect($d['basic']['name'])->toBe('Child Member')
        ->and($d['basic']['plan'])->toBe('VIP')
        ->and($d['basic']['sponsor'])->toBe($this->me->username)
        ->and($d['basic']['level'])->toBe(1)
        ->and($d['team']['direct'])->toBe(1)
        ->and($d['team']['levels'][0])->toBe(['level' => 1, 'have' => 1, 'need' => 10])
        ->and($d['earnings']['ROI'])->toEqual(12.5)
        ->and($d['contact'])->toBeNull();
});

it('does not show members outside your team', function () {
    $this->actingAs($this->me)->getJson(route('genealogy.team.member', $this->other))->assertForbidden();
    $this->actingAs($this->child)->getJson(route('genealogy.team.member', $this->me))->assertForbidden(); // upline is not your team
});

it('shows contact details to admins only', function () {
    Role::findOrCreate('admin');
    $admin = ocUser();
    $admin->assignRole('admin');

    $d = $this->actingAs($admin)->getJson(route('genealogy.team.member', $this->child))->assertOk()->json();
    expect($d['contact']['email'])->toBe('child@example.com');
});

it('renders My Saving Team as an org chart with only the saving team', function () {
    $saver = ocUser(['name' => 'Saving Child', 'account_type' => 'saving', 'sponsor_id' => $this->me->id]);
    $deep  = ocUser(['name' => 'Saving Grand', 'account_type' => 'saving', 'sponsor_id' => $saver->id]);
    DB::table('referral_trees')->insert([
        ['ancestor_id' => $this->me->id, 'descendant_id' => $saver->id, 'level' => 1, 'tree_type' => 'saving'],
        ['ancestor_id' => $this->me->id, 'descendant_id' => $deep->id,  'level' => 2, 'tree_type' => 'saving'],
        ['ancestor_id' => $saver->id,    'descendant_id' => $deep->id,  'level' => 1, 'tree_type' => 'saving'],
    ]);
    $this->me->update(['saving_enrolled' => true, 'saving_enrollment_activated' => true]);

    $html = $this->actingAs($this->me)->get(route('genealogy.my.saving.tree'))->assertOk()
        ->assertSee('id="ocWrap"', false)
        ->assertSee('Saving Child')->assertSee('Saving Grand')
        ->assertDontSee('Child Member')                      // standard-only member is not in the saving tree
        ->assertSee('tree=saving', false)
        ->getContent();
    expect($html)->toContain('"parent":' . $saver->id);     // grand child hangs under its saving parent

    $d = $this->actingAs($this->me)->getJson(route('genealogy.team.member', ['member' => $saver->id, 'tree' => 'saving']))->assertOk()->json();
    expect($d['team']['tree'])->toBe('saving')
        ->and($d['team']['levels'][0])->toBe(['level' => 1, 'have' => 1, 'need' => null])
        ->and($d['basic']['level'])->toBe(1);
});

it('opens the details panel from the right', function () {
    $this->actingAs($this->me)->get(route('genealogy.team'))->assertOk()
        ->assertSee('.oc-panel { position: fixed; right: 0;', false);
});
