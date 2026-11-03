<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    expect(DB::connection()->getDatabaseName())->not->toBeIn(['oracle_18', 'oracle_19']);
    Setting::query()->insert([
        'saving_registration_fee' => 5, 'saving_min_deposit' => 10, 'saving_monthly_instalment' => 10, 'saving_plan_months' => 25,
        'saving_roi_daily_rate' => 0.13, 'saving_commission_l7' => 1, 'saving_campaign_enabled' => 1, 'saving_campaign_l1' => 9,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    Role::findOrCreate('admin');
    $this->admin = User::factory()->create(['username' => 'ssadmin_' . uniqid(), 'transaction_id' => 'T-' . uniqid(), 'phone_number' => '03' . rand(100000000, 999999999), 'can_login' => true, 'blocked' => false]);
    $this->admin->assignRole('admin');
});

it('saves Registration & Plan on its own without asking for commission levels', function () {
    $this->actingAs($this->admin)
        ->post(route('setting.saving.update'), [
            'section' => 'registration',
            'saving_registration_fee' => 6, 'saving_min_deposit' => 12, 'saving_monthly_instalment' => 11, 'saving_plan_months' => 24,
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Registration & Plan settings updated successfully.');

    $s = Setting::first();
    expect((float) $s->saving_registration_fee)->toBe(6.0)
        ->and((int) $s->saving_plan_months)->toBe(24)
        ->and((float) $s->saving_roi_daily_rate)->toBe(0.13)   // other sections untouched
        ->and((float) $s->saving_commission_l7)->toBe(1.0)
        ->and((bool) $s->saving_campaign_enabled)->toBeTrue()
        ->and((float) $s->saving_campaign_l1)->toBe(9.0);
});

it('saves Daily ROI on its own', function () {
    $this->actingAs($this->admin)
        ->post(route('setting.saving.update'), ['section' => 'roi', 'saving_roi_daily_rate' => 0.2])
        ->assertSessionHasNoErrors();

    expect((float) Setting::first()->saving_roi_daily_rate)->toBe(0.2)
        ->and((float) Setting::first()->saving_registration_fee)->toBe(5.0);
});

it('shows a separate save button for each section', function () {
    $this->actingAs($this->admin)->get(route('setting.saving'))->assertOk()
        ->assertSee('Save Registration &amp; Plan', false)
        ->assertSee('Save Daily ROI');
});
