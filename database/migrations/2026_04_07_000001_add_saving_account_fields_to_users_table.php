<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['standard_investment', 'saving'])->default('standard_investment')->after('user_plan');
            $table->date('saving_plan_start_date')->nullable()->after('account_type');
            $table->boolean('saving_registration_completed')->default(false)->after('saving_plan_start_date');
            $table->decimal('saving_total_deposited', 10, 2)->default(0)->after('saving_registration_completed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'saving_plan_start_date', 'saving_registration_completed', 'saving_total_deposited']);
        });
    }
};
