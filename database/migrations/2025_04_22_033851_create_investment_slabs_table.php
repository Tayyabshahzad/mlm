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
        Schema::create('investment_slabs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->integer('slab_count')->default(1);
            $table->decimal('amount', 10, 2);
            $table->decimal('current_balance', 10, 2);
            $table->timestamp('achived_at');
            $table->timestamp('maturity_date'); // investment_date + 24 months
            $table->timestamp('will_pay_at'); // 1st of next month
            $table->enum('status',['No','Yes'])->default('No'); // or 'returned'
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_slabs');
    }
};
