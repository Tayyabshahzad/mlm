<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mark accounts created from Staff Roles. A staff account is never a
     * member: if it loses its staff role it must not fall back to the member
     * portal (it is logged out and deactivated instead).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_staff_account')->default(false)->after('can_login');
        });

        // Backfill: accounts already created from Staff Roles (recorded in the audit log)
        if (Schema::hasTable('instalment_payment_audits')) {
            $staffIds = DB::table('instalment_payment_audits')
                ->where('action', 'staff_account_created')
                ->pluck('user_id')->filter()->unique();

            if ($staffIds->isNotEmpty()) {
                DB::table('users')->whereIn('id', $staffIds)->update(['is_staff_account' => true]);

                // Staff accounts that have already lost every role: switch off their login now.
                $withRole = DB::table('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', \App\Models\User::class)
                    ->whereIn('model_has_roles.model_id', $staffIds)
                    ->whereIn('roles.name', ['admin', 'member', 'office-admin', 'operator'])
                    ->pluck('model_has_roles.model_id')->unique();

                DB::table('users')->whereIn('id', $staffIds->diff($withRole))->update(['can_login' => false]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_staff_account');
        });
    }
};
