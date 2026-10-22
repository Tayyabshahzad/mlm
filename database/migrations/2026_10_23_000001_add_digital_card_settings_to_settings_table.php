<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Digital card settings, editable by admins from Settings → Card Settings.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('digital_card_enabled')->default(true);
            $table->decimal('digital_card_min_amount', 12, 2)->default(1);
            $table->decimal('digital_card_max_amount', 12, 2)->default(10000);
            $table->unsignedInteger('digital_card_expiry_days')->default(365);
            $table->unsignedSmallInteger('digital_card_max_failed_pin_attempts')->default(5);
            $table->unsignedInteger('digital_card_pin_lock_minutes')->default(60);
            $table->string('digital_card_logo')->nullable(); // custom logo path under public/, null = default logo
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'digital_card_enabled',
                'digital_card_min_amount',
                'digital_card_max_amount',
                'digital_card_expiry_days',
                'digital_card_max_failed_pin_attempts',
                'digital_card_pin_lock_minutes',
                'digital_card_logo',
            ]);
        });
    }
};
