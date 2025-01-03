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
        Schema::create('p_v_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  
            $table->decimal('pv_amount', 10, 2);
            $table->string('transaction_type');
            $table->dateTime('transaction_date');
            $table->decimal('previous_balance')->default(0);  
            $table->decimal('new_balance')->default(0);;  
            $table->string('remarks')->nullable();;  
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_v_transactions');
    }
};
