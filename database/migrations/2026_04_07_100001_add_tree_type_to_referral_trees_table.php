<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_trees', function (Blueprint $table) {
            $table->string('tree_type', 20)->default('standard')->after('level');
            $table->index(['ancestor_id', 'tree_type']);
            $table->index(['descendant_id', 'tree_type']);
        });

        // Mark all existing rows as standard tree
        DB::table('referral_trees')->update(['tree_type' => 'standard']);
    }

    public function down(): void
    {
        Schema::table('referral_trees', function (Blueprint $table) {
            $table->dropIndex(['ancestor_id', 'tree_type']);
            $table->dropIndex(['descendant_id', 'tree_type']);
            $table->dropColumn('tree_type');
        });
    }
};
