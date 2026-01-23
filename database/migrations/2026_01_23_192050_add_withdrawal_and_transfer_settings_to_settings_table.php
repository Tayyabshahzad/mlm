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
            $table->decimal('min_withdrawal_limit', 10, 2)->default(25)->after('withdraw_block');
            $table->decimal('min_member_transfer', 10, 2)->default(7)->after('min_withdrawal_limit');
            $table->decimal('bank_withdrawal_fee_percent', 5, 2)->default(2)->after('min_member_transfer');
            $table->decimal('cash_withdrawal_fee_percent', 5, 2)->default(0)->after('bank_withdrawal_fee_percent');
            $table->decimal('usdt_withdrawal_discount_percent', 5, 2)->default(2)->after('cash_withdrawal_fee_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'min_withdrawal_limit',
                'min_member_transfer',
                'bank_withdrawal_fee_percent',
                'cash_withdrawal_fee_percent',
                'usdt_withdrawal_discount_percent',
            ]);
        });
    }
};
