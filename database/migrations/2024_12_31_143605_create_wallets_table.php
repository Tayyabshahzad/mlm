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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('wallet_from')->nullable();  
            $table->string('wallet_type');
            $table->decimal('balance', 15, 2)->default(0.00); // General balance for bonus, withdrawal, etc.
            $table->decimal('direct_balance', 15, 2)->default(0.00); // Direct commission balance (only for direct/indirect)
            $table->decimal('indirect_balance', 15, 2)->default(0.00);
            $table->string('commission_type');
            $table->string('level');
            $table->foreign('wallet_from')->references('id')->on('users')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
