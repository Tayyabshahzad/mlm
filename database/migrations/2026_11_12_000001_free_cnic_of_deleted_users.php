<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A deleted (soft-deleted) account kept its CNIC, so the same person could not
 * register again ("Duplicate entry ... users_cnic_unique").
 * Deleted accounts now keep their CNIC as "del<id>-<cnic>": still readable, but free to reuse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cnic', 32)->nullable()->change();
        });

        DB::table('users')
            ->whereNotNull('deleted_at')
            ->whereNotNull('cnic')
            ->where('cnic', 'not like', 'del%')
            ->orderBy('id')
            ->get(['id', 'cnic'])
            ->each(fn ($u) => DB::table('users')->where('id', $u->id)->update(['cnic' => 'del' . $u->id . '-' . $u->cnic]));
    }

    public function down(): void
    {
        // Put the original CNIC back only where nobody has taken it since.
        DB::table('users')->whereNotNull('deleted_at')->where('cnic', 'like', 'del%')->get(['id', 'cnic'])
            ->each(function ($u) {
                $original = preg_replace('/^del\d+-/', '', $u->cnic);
                if (!DB::table('users')->where('cnic', $original)->exists()) {
                    DB::table('users')->where('id', $u->id)->update(['cnic' => $original]);
                }
            });
    }
};
