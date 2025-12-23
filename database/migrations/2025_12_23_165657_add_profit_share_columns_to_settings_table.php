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
            // Standard Package Profit Share Percentages (7 levels)
            $table->decimal('standard_profit_l1', 5, 2)->default(7.00)->after('vip_commission_l7')
                ->comment('Standard Level 1 profit share percentage');
            $table->decimal('standard_profit_l2', 5, 2)->default(6.00)->after('standard_profit_l1')
                ->comment('Standard Level 2 profit share percentage');
            $table->decimal('standard_profit_l3', 5, 2)->default(5.00)->after('standard_profit_l2')
                ->comment('Standard Level 3 profit share percentage');
            $table->decimal('standard_profit_l4', 5, 2)->default(4.00)->after('standard_profit_l3')
                ->comment('Standard Level 4 profit share percentage');
            $table->decimal('standard_profit_l5', 5, 2)->default(3.00)->after('standard_profit_l4')
                ->comment('Standard Level 5 profit share percentage');
            $table->decimal('standard_profit_l6', 5, 2)->default(2.00)->after('standard_profit_l5')
                ->comment('Standard Level 6 profit share percentage');
            $table->decimal('standard_profit_l7', 5, 2)->default(1.00)->after('standard_profit_l6')
                ->comment('Standard Level 7 profit share percentage');

            // VIP Package Profit Share Percentages (7 levels) - Half of Standard
            $table->decimal('vip_profit_l1', 5, 2)->default(3.50)->after('standard_profit_l7')
                ->comment('VIP Level 1 profit share percentage');
            $table->decimal('vip_profit_l2', 5, 2)->default(3.00)->after('vip_profit_l1')
                ->comment('VIP Level 2 profit share percentage');
            $table->decimal('vip_profit_l3', 5, 2)->default(2.50)->after('vip_profit_l2')
                ->comment('VIP Level 3 profit share percentage');
            $table->decimal('vip_profit_l4', 5, 2)->default(2.00)->after('vip_profit_l3')
                ->comment('VIP Level 4 profit share percentage');
            $table->decimal('vip_profit_l5', 5, 2)->default(1.50)->after('vip_profit_l4')
                ->comment('VIP Level 5 profit share percentage');
            $table->decimal('vip_profit_l6', 5, 2)->default(1.00)->after('vip_profit_l5')
                ->comment('VIP Level 6 profit share percentage');
            $table->decimal('vip_profit_l7', 5, 2)->default(0.50)->after('vip_profit_l6')
                ->comment('VIP Level 7 profit share percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'standard_profit_l1', 'standard_profit_l2', 'standard_profit_l3',
                'standard_profit_l4', 'standard_profit_l5', 'standard_profit_l6', 'standard_profit_l7',
                'vip_profit_l1', 'vip_profit_l2', 'vip_profit_l3',
                'vip_profit_l4', 'vip_profit_l5', 'vip_profit_l6', 'vip_profit_l7'
            ]);
        });
    }
};
