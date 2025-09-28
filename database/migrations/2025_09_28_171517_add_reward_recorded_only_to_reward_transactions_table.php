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
        Schema::table('reward_transactions', function (Blueprint $table) {
            // Modify the transaction_type enum to include 'reward_recorded_only'
            $table->enum('transaction_type', ['reward_assigned', 'reward_reversed', 'reward_recorded_only'])
                  ->default('reward_assigned')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reward_transactions', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('transaction_type', ['reward_assigned', 'reward_reversed'])
                  ->default('reward_assigned')
                  ->change();
        });
    }
};
