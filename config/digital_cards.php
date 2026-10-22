<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Digital Cards
    |--------------------------------------------------------------------------
    |
    | Cards are generated from a member's online wallet, shared with one
    | eligible downline member, and used by that member to pay a saving-plan
    | instalment (subject to admin confirmation).
    |
    | NOTE: enabled, min/max amount, expiry days, PIN-lock rules and the logo
    | are managed by admins in Settings → Card Settings (stored in the
    | `settings` table). The values below are only fallback defaults used
    | when those database values are missing.
    |
    */

    'enabled' => env('DIGITAL_CARDS_ENABLED', true),

    // Minimum / maximum face value of a single card (USD).
    'min_amount' => (float) env('DIGITAL_CARD_MIN_AMOUNT', 1),
    'max_amount' => (float) env('DIGITAL_CARD_MAX_AMOUNT', 10000),

    // Days a card stays valid after generation. Unused cards that pass this
    // date are expired by `php artisan cards:expire` and refunded to the
    // generator's online wallet.
    'expiry_days' => (int) env('DIGITAL_CARD_EXPIRY_DAYS', 365), // 1 year

    // Consecutive wrong-PIN attempts on one card before it is temporarily
    // refused for payment (counted from the card's event history).
    'max_failed_pin_attempts' => 5,
    'failed_pin_window_minutes' => 60,

    // Company logo printed top-right on the card (transparent PNG, relative to public/).
    'logo_image' => 'assets/custom-images/logo-white.png',

    // Fallback text used only if the logo file above is missing.
    'brand_line_1' => env('DIGITAL_CARD_BRAND_1', 'GVI'),
    'brand_line_2' => env('DIGITAL_CARD_BRAND_2', 'INTERNATIONAL'),

    // Optional background artwork (e.g. a world-map texture). Path relative to
    // public/. When the file is missing a generated background is used.
    'background_image' => 'assets/custom-images/digital-card-bg.png',
];
