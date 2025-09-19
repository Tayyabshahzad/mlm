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
            $table->decimal('agreement_balance', 15, 2)->default(0)->after('agreement_sent');
            $table->boolean('agreement_balance_locked')->default(true)->after('agreement_balance');
            $table->integer('current_rank_level')->default(0)->after('agreement_balance_locked');
            $table->boolean('eligible_for_binary_2x')->default(false)->after('current_rank_level');
            $table->boolean('eligible_for_binary_7x')->default(false)->after('eligible_for_binary_2x');
            $table->decimal('total_binary_earnings', 15, 2)->default(0)->after('eligible_for_binary_7x');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'agreement_balance',
                'agreement_balance_locked',
                'current_rank_level',
                'eligible_for_binary_2x',
                'eligible_for_binary_7x',
                'total_binary_earnings'
            ]);
        });
    }
};
