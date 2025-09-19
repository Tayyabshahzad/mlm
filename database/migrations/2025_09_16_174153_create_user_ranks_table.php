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
        Schema::create('user_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rank_name');
            $table->integer('rank_level')->default(0);
            $table->boolean('eligible_for_2x')->default(false);
            $table->boolean('eligible_for_7x')->default(false);
            $table->decimal('required_investment', 15, 2)->default(0);
            $table->integer('required_direct_referrals')->default(0);
            $table->integer('required_team_size')->default(0);
            $table->json('rank_benefits')->nullable(); // Benefits like higher commission rates
            $table->timestamp('achieved_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['eligible_for_2x', 'eligible_for_7x']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ranks');
    }
};
