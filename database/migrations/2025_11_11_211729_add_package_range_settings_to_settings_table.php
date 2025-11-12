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
            $table->decimal('standard_package_min', 10, 2)->default(50)->after('registration_fee');
            $table->decimal('standard_package_max', 10, 2)->default(344)->after('standard_package_min');
            $table->decimal('vip_package_min', 10, 2)->default(345)->after('standard_package_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['standard_package_min', 'standard_package_max', 'vip_package_min']);
        });
    }
};
