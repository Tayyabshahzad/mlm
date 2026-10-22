<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Single source for digital card settings.
 *
 * Values come from the `settings` table (Settings → Card Settings in the admin
 * panel). If the row/column is missing, the defaults in config/digital_cards.php
 * are used, so the feature keeps working before the migration has run.
 */
class DigitalCardSettings
{
    public static function enabled(): bool
    {
        return (bool) self::value('digital_card_enabled', config('digital_cards.enabled', true));
    }

    public static function minAmount(): float
    {
        return (float) self::value('digital_card_min_amount', config('digital_cards.min_amount', 1));
    }

    public static function maxAmount(): float
    {
        return (float) self::value('digital_card_max_amount', config('digital_cards.max_amount', 10000));
    }

    public static function expiryDays(): int
    {
        return (int) self::value('digital_card_expiry_days', config('digital_cards.expiry_days', 365));
    }

    public static function maxFailedPinAttempts(): int
    {
        return (int) self::value('digital_card_max_failed_pin_attempts', config('digital_cards.max_failed_pin_attempts', 5));
    }

    public static function pinLockMinutes(): int
    {
        return (int) self::value('digital_card_pin_lock_minutes', config('digital_cards.failed_pin_window_minutes', 60));
    }

    /** Logo path relative to public/ — the admin-uploaded logo, else the default. */
    public static function logoPath(): string
    {
        $custom = self::value('digital_card_logo', null);
        if ($custom && is_file(public_path($custom))) {
            return $custom;
        }

        return (string) config('digital_cards.logo_image', 'assets/custom-images/logo-white.png');
    }

    public static function hasCustomLogo(): bool
    {
        $custom = self::value('digital_card_logo', null);

        return $custom && is_file(public_path($custom));
    }

    /** Human-readable validity, e.g. "1 year" or "45 days". */
    public static function validityLabel(): string
    {
        $days = self::expiryDays();

        if ($days > 0 && $days % 365 === 0) {
            $years = intdiv($days, 365);
            return $years . ' ' . ($years === 1 ? 'year' : 'years');
        }

        return $days . ' ' . ($days === 1 ? 'day' : 'days');
    }

    private static function value(string $column, mixed $default): mixed
    {
        static $hasColumns = null;
        $hasColumns ??= Schema::hasColumn('settings', 'digital_card_enabled');

        if (!$hasColumns) {
            return $default;
        }

        $value = Setting::query()->value($column);

        return $value ?? $default;
    }
}
