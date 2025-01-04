<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('r_o_i_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Reference to the user
            $table->decimal('amount', 10, 2); // ROI amount
            $table->decimal('percentage', 5, 2); // Percentage applied for this ROI
            $table->string('description')->nullable(); // Description of the transaction
            $table->timestamp('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP')); // Timestamp of transaction
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Foreign key to users table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_o_i_transactions');
    }
};
