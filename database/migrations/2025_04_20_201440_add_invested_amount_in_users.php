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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('transferred_amount', 10, 2)->default(0);
            $table->decimal('converted_usdt_amount', 10, 2)->default(0);
            $table->decimal('fee_deducted', 10, 2)->default(0);
            $table->decimal('net_invested_usdt_amount', 12, 2)->default(0);
            $table->decimal('negative_pv', 10, 2)->default(0); 
            $table->decimal('usdt_rate', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        Schema::table('users', function (Blueprint $table) { 
            $table->decimal('transferred_amount', 10, 2)->default(0);
            $table->decimal('converted_usdt_amount', 10, 2)->default(0);
            $table->decimal('fee_deducted', 10, 2)->default(0);
            $table->decimal('net_invested_usdt_amount', 12, 2)->default(0);
            $table->decimal('negative_pv', 10, 2)->default(0); 
            $table->decimal('usdt_rate', 10, 2)->default(0);
        });
    }
};
