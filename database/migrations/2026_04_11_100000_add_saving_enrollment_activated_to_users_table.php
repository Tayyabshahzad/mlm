<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tracks whether an enrolled standard user has been admin-activated
            // for the Savings Program. Separate from can_login (which gates their
            // standard account). ROI and commissions only fire when this is true.
            $table->boolean('saving_enrollment_activated')->default(false)->after('saving_enrolled');
            $table->timestamp('saving_enrollment_activated_at')->nullable()->after('saving_enrollment_activated');
            $table->unsignedBigInteger('saving_enrollment_activated_by')->nullable()->after('saving_enrollment_activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'saving_enrollment_activated',
                'saving_enrollment_activated_at',
                'saving_enrollment_activated_by',
            ]);
        });
    }
};
