<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_instalment_commissions', function (Blueprint $table) {
            $table->boolean('excess_reversed')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('saving_instalment_commissions', function (Blueprint $table) {
            $table->dropColumn('excess_reversed');
        });
    }
};
