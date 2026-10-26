<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keep the admin's rejection reason on the sheet row itself, so the
     * operator still sees why after the instalment has moved on.
     */
    public function up(): void
    {
        Schema::table('instalment_upload_rows', function (Blueprint $table) {
            $table->string('rejected_reason', 500)->nullable()->after('amount');
            $table->timestamp('rejected_at')->nullable()->after('rejected_reason');
        });
    }

    public function down(): void
    {
        Schema::table('instalment_upload_rows', function (Blueprint $table) {
            $table->dropColumn(['rejected_reason', 'rejected_at']);
        });
    }
};
