<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deleted accounts kept their referral link, so the same person could not register
 * again ("Duplicate entry ... referral_links_link_unique"). Free them as "del<user_id>-<link>".
 * Also frees any CNIC still held (same as 2026_11_12_000001, in case it ran before a delete).
 */
return new class extends Migration
{
    public function up(): void
    {
        $deleted = DB::table('users')->whereNotNull('deleted_at')->pluck('id');

        DB::table('referral_links')
            ->whereIn('user_id', $deleted)
            ->where('link', 'not like', 'del%')
            ->update(['link' => DB::raw("CONCAT('del', user_id, '-', link)"), 'is_active' => false]);

        DB::table('users')
            ->whereNotNull('deleted_at')->whereNotNull('cnic')->where('cnic', 'not like', 'del%')
            ->update(['cnic' => DB::raw("CONCAT('del', id, '-', cnic)")]);
    }

    public function down(): void
    {
        // Put links back only where nobody has taken them since.
        DB::table('referral_links')->where('link', 'like', 'del%')->get(['id', 'link'])
            ->each(function ($r) {
                $original = preg_replace('/^del\d+-/', '', $r->link);
                if (!DB::table('referral_links')->where('link', $original)->exists()) {
                    DB::table('referral_links')->where('id', $r->id)->update(['link' => $original]);
                }
            });
    }
};
