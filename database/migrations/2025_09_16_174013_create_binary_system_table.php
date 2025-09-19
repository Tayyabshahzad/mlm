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
        Schema::create('binary_system', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('system_type', ['2x', '7x']);
            $table->integer('current_level')->default(1);
            $table->decimal('investment_amount', 15, 2)->default(0);
            $table->decimal('total_earned', 15, 2)->default(0);
            $table->decimal('current_limit', 15, 2)->default(0);
            $table->boolean('auto_next_level')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->json('level_history')->nullable(); // Track level completion history
            $table->timestamps();

            $table->index(['user_id', 'system_type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('binary_system');
    }
};
