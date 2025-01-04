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
        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('source_user_id');
            $table->unsignedInteger('level');
            $table->decimal('percentage', 5, 2);
            $table->decimal('pv_value', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->timestamp('earned_date'); 

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('source_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_logs');
    }
};
