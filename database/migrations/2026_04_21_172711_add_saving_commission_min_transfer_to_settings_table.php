<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('saving_commission_min_transfer', 10, 2)->default(10.70)->after('saving_roi_daily_rate');
        });

        // Set the default on the existing settings row
        DB::table('settings')->update(['saving_commission_min_transfer' => 10.70]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('saving_commission_min_transfer');
        });
    }
};
