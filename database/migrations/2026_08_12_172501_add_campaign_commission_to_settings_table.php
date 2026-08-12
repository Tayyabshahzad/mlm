<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('saving_campaign_enabled')->default(false)->after('saving_commission_l7');
            $table->date('saving_campaign_start')->nullable()->after('saving_campaign_enabled');
            $table->date('saving_campaign_end')->nullable()->after('saving_campaign_start');
            $table->string('saving_campaign_label')->nullable()->after('saving_campaign_end');
            $table->decimal('saving_campaign_l1', 8, 2)->nullable()->after('saving_campaign_label');
            $table->decimal('saving_campaign_l2', 8, 2)->nullable()->after('saving_campaign_l1');
            $table->decimal('saving_campaign_l3', 8, 2)->nullable()->after('saving_campaign_l2');
            $table->decimal('saving_campaign_l4', 8, 2)->nullable()->after('saving_campaign_l3');
            $table->decimal('saving_campaign_l5', 8, 2)->nullable()->after('saving_campaign_l4');
            $table->decimal('saving_campaign_l6', 8, 2)->nullable()->after('saving_campaign_l5');
            $table->decimal('saving_campaign_l7', 8, 2)->nullable()->after('saving_campaign_l6');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'saving_campaign_enabled',
                'saving_campaign_start',
                'saving_campaign_end',
                'saving_campaign_label',
                'saving_campaign_l1',
                'saving_campaign_l2',
                'saving_campaign_l3',
                'saving_campaign_l4',
                'saving_campaign_l5',
                'saving_campaign_l6',
                'saving_campaign_l7',
            ]);
        });
    }
};
