<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stores the raw amount the user paid at saving enrollment/registration
            // (distinct from converted_usdt_amount which belongs to the standard investment)
            $table->decimal('saving_initial_payment', 10, 2)->default(0)->after('saving_total_deposited');
            $table->decimal('saving_initial_fee', 10, 2)->default(0)->after('saving_initial_payment');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['saving_initial_payment', 'saving_initial_fee']);
        });
    }
};
