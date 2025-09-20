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
        Schema::table('r_o_i_transactions', function (Blueprint $table) {
            $table->string('trigger_reason')->nullable()->after('description')->comment('Reason for ROI trigger: topup_trigger, manual_command, scheduled_auto_roi, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('r_o_i_transactions', function (Blueprint $table) {
            $table->dropColumn('trigger_reason');
        });
    }
};
