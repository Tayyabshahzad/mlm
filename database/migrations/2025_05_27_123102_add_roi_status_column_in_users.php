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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('roi_status', ['active', 'stopped'])->default('active')->after('can_login'); 
            $table->timestamp('account_stopped_at')->nullable()->after('roi_status');
            $table->string('stop_reason')->nullable()->after('stop_reason'); 
             $table->string('stop_reason_description')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'roi_status',
                'account_stopped_at',
                'stop_reason'
            ]);
        });
    }
};
