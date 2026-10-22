<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalCardEvent;
use App\Models\Setting;
use App\Support\DigitalCardSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DigitalCardSettingsController extends Controller
{
    private const LOGO_DIR = 'uploads/digital-cards';

    public function index()
    {
        return view('setting.digital-cards', [
            'setting'       => Setting::first(),
            'logoPath'      => DigitalCardSettings::logoPath(),
            'hasCustomLogo' => DigitalCardSettings::hasCustomLogo(),
            'lockedCards'   => app(\App\Services\DigitalCardService::class)->pinLockedCards(),
            'recentChanges' => DigitalCardEvent::with('actor:id,name,username')
                ->where('event', DigitalCardEvent::SETTINGS_UPDATED)
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'digital_card_min_amount'              => 'required|numeric|min:0.01|max:1000000',
            'digital_card_max_amount'              => 'required|numeric|gte:digital_card_min_amount|max:1000000',
            'digital_card_expiry_days'             => 'required|integer|min:1|max:3650',
            'digital_card_max_failed_pin_attempts' => 'required|integer|min:1|max:50',
            'digital_card_pin_lock_minutes'        => 'required|integer|min:1|max:10080',
            'logo'                                 => 'nullable|image|mimes:png|max:2048',
            'remove_logo'                          => 'nullable|boolean',
        ], [
            'digital_card_max_amount.gte' => 'Maximum amount must be greater than or equal to the minimum amount.',
            'logo.mimes'                  => 'The logo must be a PNG file (transparent background recommended).',
        ]);

        $setting = Setting::first() ?? Setting::create([]);

        $values = [
            'digital_card_enabled'                 => $request->boolean('digital_card_enabled'),
            'digital_card_min_amount'              => round((float) $data['digital_card_min_amount'], 2),
            'digital_card_max_amount'              => round((float) $data['digital_card_max_amount'], 2),
            'digital_card_expiry_days'             => (int) $data['digital_card_expiry_days'],
            'digital_card_max_failed_pin_attempts' => (int) $data['digital_card_max_failed_pin_attempts'],
            'digital_card_pin_lock_minutes'        => (int) $data['digital_card_pin_lock_minutes'],
        ];

        $oldLogo = $setting->digital_card_logo;
        if ($request->hasFile('logo')) {
            File::ensureDirectoryExists(public_path(self::LOGO_DIR));
            $name = 'card-logo-' . now()->format('YmdHis') . '.png';
            $request->file('logo')->move(public_path(self::LOGO_DIR), $name);
            $values['digital_card_logo'] = self::LOGO_DIR . '/' . $name;
        } elseif ($request->boolean('remove_logo')) {
            $values['digital_card_logo'] = null;
        }

        // Audit: record exactly what changed and who changed it.
        $changes = [];
        foreach ($values as $key => $new) {
            $old = $setting->{$key};
            if ((string) $old !== (string) $new && !(is_numeric($old) && is_numeric($new) && (float) $old === (float) $new)) {
                $changes[$key] = ['from' => $old, 'to' => $new];
            }
        }

        $setting->update($values);

        if (array_key_exists('digital_card_logo', $values) && $oldLogo && $oldLogo !== $values['digital_card_logo']
            && str_starts_with($oldLogo, self::LOGO_DIR . '/')) {
            File::delete(public_path($oldLogo));
        }

        if ($changes) {
            DigitalCardEvent::create([
                'event'       => DigitalCardEvent::SETTINGS_UPDATED,
                'actor_id'    => Auth::id(),
                'description' => 'Card settings updated: ' . implode(', ', array_map(
                    fn ($k) => str_replace('digital_card_', '', $k), array_keys($changes)
                )),
                'meta'        => $changes,
                'ip_address'  => $request->ip(),
            ]);
            Log::info('Digital card settings updated by user ' . Auth::id(), $changes);
        }

        return back()->with('success', $changes ? 'Card settings updated.' : 'No changes to save.');
    }
}
