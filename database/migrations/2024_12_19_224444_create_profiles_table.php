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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable(); // e.g., Male, Female, Other
            $table->date('date_of_birth')->nullable();

             // Contact Details
            $table->string('phone')->nullable();
            $table->string('email')->nullable(); // Alternative email
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();

             // Additional Information
            $table->string('occupation')->nullable(); // Job title or role
            $table->text('bio')->nullable();          // Short biography or about section
            $table->json('skills')->nullable();       // List of skills as JSON

            // Bank Information
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ibn_number')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('bank_name')->nullable();
            
            $table->boolean('is_public')->default(true); // Whether the profile is public

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
