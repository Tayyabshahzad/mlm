<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original primary key (ancestor_id, descendant_id) does NOT include tree_type.
 * This means a user can only appear once per (ancestor, descendant) pair across ALL
 * tree types. When an existing standard-tree user enrolls in the saving plan,
 * insertOrIgnore() silently skips the saving-tree rows because the PK already exists
 * for the standard tree — leaving them with no saving sponsor.
 *
 * Fix: change the PK to (ancestor_id, descendant_id, tree_type) so that a pair can
 * exist independently in the standard tree and the saving tree.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows that would violate the new PK before altering.
        // Because insertOrIgnore was used, duplicates shouldn't exist, but we
        // clean defensively: keep the row with the lower level per unique triple.
        DB::statement('
            DELETE r1 FROM referral_trees r1
            INNER JOIN referral_trees r2
                ON  r1.ancestor_id   = r2.ancestor_id
                AND r1.descendant_id = r2.descendant_id
                AND r1.tree_type     = r2.tree_type
                AND r1.level         > r2.level
        ');

        Schema::table('referral_trees', function (Blueprint $table) {
            // Drop the old 2-column primary key
            $table->dropPrimary(['ancestor_id', 'descendant_id']);

            // New 3-column primary key includes tree_type
            $table->primary(['ancestor_id', 'descendant_id', 'tree_type']);
        });
    }

    public function down(): void
    {
        // Remove any saving-tree rows that would conflict when reverting
        DB::statement('
            DELETE FROM referral_trees
            WHERE tree_type != \'standard\'
              AND (ancestor_id, descendant_id) IN (
                  SELECT ancestor_id, descendant_id
                  FROM (SELECT ancestor_id, descendant_id FROM referral_trees WHERE tree_type = \'standard\') AS std
              )
        ');

        Schema::table('referral_trees', function (Blueprint $table) {
            $table->dropPrimary(['ancestor_id', 'descendant_id', 'tree_type']);
            $table->primary(['ancestor_id', 'descendant_id']);
        });
    }
};
