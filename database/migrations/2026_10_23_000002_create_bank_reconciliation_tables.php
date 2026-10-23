<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // One row per bank transaction matched to a saving instalment by an Office Admin.
        Schema::create('bank_payment_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_instalment_id')->constrained('saving_instalments');
            $table->foreignId('user_id')->constrained('users');               // member who paid
            $table->string('bank_reference', 100);                              // as typed by the officer
            // Normalised reference (upper-case, no spaces) while the match is active;
            // set to NULL on reversal. UNIQUE → one bank transaction can only ever
            // settle one instalment at a time.
            $table->string('reference_key', 100)->nullable()->unique();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('bank_name', 100)->nullable();
            $table->string('source', 20);                                       // user_submission | office_entry
            $table->string('user_submitted_reference', 100)->nullable();        // what the member typed, if any
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('verified');                  // verified | reversed
            $table->foreignId('verified_by')->constrained('users');
            $table->timestamp('verified_at');
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->timestamp('reversed_at')->nullable();
            $table->string('reversal_reason', 1000)->nullable();
            $table->timestamps();

            $table->index(['saving_instalment_id', 'status']);
            $table->index('payment_date');
            $table->index('verified_at');
        });

        // Append-only audit trail for payment updates (never updated or deleted).
        Schema::create('instalment_payment_audits', function (Blueprint $table) {
            $table->id();
            $table->string('action', 40);
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->unsignedBigInteger('user_id')->nullable();                  // member concerned
            $table->unsignedBigInteger('saving_instalment_id')->nullable();
            $table->unsignedBigInteger('reconciliation_id')->nullable();
            $table->string('description', 1000)->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['saving_instalment_id', 'id']);
            $table->index('action');
            $table->index('actor_id');
        });

        Role::findOrCreate('office-admin', 'web');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('instalment_payment_audits');
        Schema::dropIfExists('bank_payment_reconciliations');
        Role::where('name', 'office-admin')->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
