<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // null = KYC not requested, pending = requested by admin, submitted = user uploaded,
            // approved = admin approved, rejected = admin rejected
            $table->string('kyc_status', 20)->nullable()->after('cnic');
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_status');
            $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_rejection_reason');
            $table->timestamp('kyc_reviewed_at')->nullable()->after('kyc_submitted_at');
            $table->unsignedBigInteger('kyc_reviewed_by')->nullable()->after('kyc_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kyc_status', 'kyc_rejection_reason', 'kyc_submitted_at', 'kyc_reviewed_at', 'kyc_reviewed_by']);
        });
    }
};
