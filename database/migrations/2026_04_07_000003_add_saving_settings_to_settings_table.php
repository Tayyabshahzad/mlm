<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('saving_parent_user_id')->nullable()->after('id');
            $table->decimal('saving_registration_fee', 8, 2)->default(5)->after('saving_parent_user_id');
            $table->decimal('saving_min_deposit', 8, 2)->default(19)->after('saving_registration_fee');
            $table->decimal('saving_monthly_instalment', 8, 2)->default(19)->after('saving_min_deposit');
            $table->unsignedTinyInteger('saving_plan_months')->default(25)->after('saving_monthly_instalment');
            // Saving account commission rates per level
            $table->decimal('saving_commission_l1', 5, 2)->default(7.00)->after('saving_plan_months');
            $table->decimal('saving_commission_l2', 5, 2)->default(2.00)->after('saving_commission_l1');
            $table->decimal('saving_commission_l3', 5, 2)->default(1.00)->after('saving_commission_l2');
            $table->decimal('saving_commission_l4', 5, 2)->default(1.00)->after('saving_commission_l3');
            $table->decimal('saving_commission_l5', 5, 2)->default(1.00)->after('saving_commission_l4');
            $table->decimal('saving_commission_l6', 5, 2)->default(1.00)->after('saving_commission_l5');
            $table->decimal('saving_commission_l7', 5, 2)->default(1.00)->after('saving_commission_l6');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'saving_parent_user_id',
                'saving_registration_fee',
                'saving_min_deposit',
                'saving_monthly_instalment',
                'saving_plan_months',
                'saving_commission_l1',
                'saving_commission_l2',
                'saving_commission_l3',
                'saving_commission_l4',
                'saving_commission_l5',
                'saving_commission_l6',
                'saving_commission_l7',
            ]);
        });
    }
};
