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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('phone_number', 15)->index(); // Phone number to which OTP is sent
            $table->string('otp', 6); // OTP code
            $table->integer('otpAttempts')->default(1); //
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('expires_at'); // OTP expiration time
            $table->boolean('is_verified')->default(false); // Verification status
            
            
            $table->timestamps(); 

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
