<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_instalment_commission_configs', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('instalment_number')->unsigned(); // 1–25
            $table->tinyInteger('level')->unsigned();             // 1–7
            $table->decimal('percentage', 8, 4);
            $table->timestamps();

            $table->unique(['instalment_number', 'level'], 'sicc_inst_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_instalment_commission_configs');
    }
};
