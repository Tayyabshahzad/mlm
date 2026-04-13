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
            // Marks any user (standard or saving account_type) who has joined the
            // Savings Program. Standard users keep account_type='standard_investment'
            // but gain saving tree membership, instalment schedule, and saving ROI.
            $table->boolean('saving_enrolled')->default(false)->after('saving_total_deposited');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('saving_enrolled');
        });
    }
};
