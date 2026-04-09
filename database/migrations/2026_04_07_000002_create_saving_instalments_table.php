<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_instalments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('instalment_number'); // 1–25
            $table->decimal('amount', 10, 2);                 // amount due for this instalment
            $table->date('due_date');                         // scheduled due date
            $table->enum('status', ['pending', 'submitted', 'confirmed', 'missed'])->default('pending');
            $table->boolean('is_late')->default(false);       // paid after due_date
            $table->boolean('deposit_deferred')->default(false); // deposit held until next cycle
            $table->date('next_cycle_date')->nullable();      // when deferred deposit will credit
            $table->string('transaction_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('submitted_amount', 10, 2)->nullable(); // amount user actually paid
            $table->timestamp('submitted_at')->nullable();    // when user submitted proof
            $table->timestamp('confirmed_at')->nullable();    // when admin confirmed
            $table->unsignedBigInteger('confirmed_by')->nullable(); // admin user id
            $table->timestamp('deposited_at')->nullable();    // when amount credited to wallet
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'instalment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_instalments');
    }
};
