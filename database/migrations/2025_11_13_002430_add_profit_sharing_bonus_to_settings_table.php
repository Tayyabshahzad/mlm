<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Profit sharing bonus multipliers for VIP and Standard plans
            $table->decimal('standard_profit_multiplier', 5, 2)->default(1.00)->after('vip_package_min')
                ->comment('Profit sharing multiplier for Standard users (e.g., 1.00 = 100%, 1.5 = 150%)');

            $table->decimal('vip_profit_multiplier', 5, 2)->default(1.50)->after('standard_profit_multiplier')
                ->comment('Profit sharing multiplier for VIP users (e.g., 1.50 = 150%, 2.0 = 200%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['standard_profit_multiplier', 'vip_profit_multiplier']);
        });
    }
};
