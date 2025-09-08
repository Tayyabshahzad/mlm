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
        Schema::create('reward_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique(); // 1, 2, 3, 4, 5, 6, 7
            $table->decimal('reward_amount', 10, 2); // $130, $350, etc.
            $table->integer('users_required'); // 10, 20, 30, etc.
            $table->string('description')->nullable(); // Level description
            $table->boolean('is_active')->default(true); // Enable/disable levels
            $table->json('additional_settings')->nullable(); // Extra settings as JSON
            $table->timestamps();
            
            $table->index(['level', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_settings');
    }
};
