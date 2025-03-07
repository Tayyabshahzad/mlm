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
        Schema::table('weeks', function (Blueprint $table) {
            $table->decimal('percentage', 5, 3)->change(); // Allow 3 decimal places
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weeks', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->change(); // Rollback to 2 decimal places
        });
    }
};
