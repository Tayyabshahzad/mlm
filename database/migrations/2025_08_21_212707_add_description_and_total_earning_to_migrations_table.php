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
        Schema::table('wallets', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->decimal('total_earning', 15, 2)->default(0);
            $table->enum('transaction_type', ['credit', 'debit','other'])->default('other');;
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['description', 'total_earning','transaction_type']);
        });
    }
};
