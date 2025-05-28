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
            $table->enum('roi_status', ['active', 'stopped'])->default('active'); 
            $table->timestamp('account_stopped_at')->nullable();
            $table->string('stop_reason')->nullable(); 
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
                'stop_reason',
                'stop_reason_description'
            ]);
        });
    }
};
