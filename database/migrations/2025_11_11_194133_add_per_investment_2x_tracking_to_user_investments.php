<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_investments', function (Blueprint $table) {
            // Track 2x commitment (2 * investment amount)
            $table->decimal('committed_amount', 10, 2)->after('amount')->default(0);

            // Track total earnings from ALL wallets for this specific investment
            $table->decimal('total_earnings', 10, 2)->default(0)->after('committed_amount');

            // ROI status for this investment: active, completed, stopped
            $table->enum('roi_status', ['active', 'completed', 'stopped'])->default('active')->after('total_earnings');

            // When this investment completed 2x
            $table->timestamp('completed_at')->nullable()->after('roi_status');

            // User's plan at time of investment (for historical reference)
            $table->enum('user_plan_at_time', ['standard', 'vip'])->nullable()->after('completed_at');
        });

        // Initialize committed_amount = amount * 2 for existing investments
        DB::statement('UPDATE user_investments SET committed_amount = amount * 2 WHERE committed_amount = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_investments', function (Blueprint $table) {
            $table->dropColumn([
                'committed_amount',
                'total_earnings',
                'roi_status',
                'completed_at',
                'user_plan_at_time'
            ]);
        });
    }
};
