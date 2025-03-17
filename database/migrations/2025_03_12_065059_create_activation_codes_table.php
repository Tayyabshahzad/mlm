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
        Schema::create('activation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('generated_by');
            $table->enum('status', ['unused', 'used', 'expired'])->default('unused');
            $table->enum('admin_approval', ['pending', 'approved', 'rejected'])->default('pending'); // Removed ->after('status')
            $table->unsignedBigInteger('used_by')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps(); 
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('used_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_codes');
    }
};
