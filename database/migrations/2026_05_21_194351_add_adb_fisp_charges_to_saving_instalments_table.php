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
        Schema::table('saving_instalments', function (Blueprint $table) {
            $table->decimal('adb_charge', 10, 4)->default(0)->nullable()->after('submitted_amount');
            $table->decimal('fisp_charge', 10, 4)->default(0)->nullable()->after('adb_charge');
            $table->decimal('net_credited', 10, 4)->nullable()->after('fisp_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saving_instalments', function (Blueprint $table) {
            $table->dropColumn(['adb_charge', 'fisp_charge', 'net_credited']);
        });
    }
};
