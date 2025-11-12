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
        Schema::table('weeks', function (Blueprint $table) {
            // Rename existing percentage to standard_percentage
            $table->renameColumn('percentage', 'standard_percentage');
        });

        // Add vip_percentage in a separate statement (Laravel limitation)
        Schema::table('weeks', function (Blueprint $table) {
            $table->decimal('vip_percentage', 5, 2)->after('standard_percentage')->default(5.00);
        });

        // Set default VIP percentage = Standard percentage for existing records
        DB::table('weeks')->update(['vip_percentage' => DB::raw('standard_percentage')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weeks', function (Blueprint $table) {
            $table->dropColumn('vip_percentage');
        });

        Schema::table('weeks', function (Blueprint $table) {
            $table->renameColumn('standard_percentage', 'percentage');
        });
    }
};
