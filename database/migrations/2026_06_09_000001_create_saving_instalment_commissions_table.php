<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saving_instalment_commissions', function (Blueprint $table) {
            $table->id();

            // The instalment that triggered this commission record
            $table->unsignedBigInteger('saving_instalment_id');
            $table->foreign('saving_instalment_id')
                  ->references('id')->on('saving_instalments')
                  ->onDelete('cascade');

            // The user who paid the instalment (commission source)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // The upline user receiving the commission
            $table->unsignedBigInteger('ancestor_id');
            $table->foreign('ancestor_id')->references('id')->on('users')->onDelete('cascade');

            // Commission metadata
            $table->unsignedTinyInteger('level');              // 1–7 in the saving tree
            $table->unsignedTinyInteger('instalment_number'); // 1–25, denormalised for fast queries
            $table->decimal('commission_amount', 10, 4);
            $table->decimal('percentage', 8, 4);
            $table->string('commission_type', 20);            // direct | indirect

            // Lifecycle
            $table->enum('status', ['pending', 'paid', 'skipped'])->default('pending');

            // Set when commission is paid (wallet entry created)
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->foreign('wallet_id')->references('id')->on('wallets')->nullOnDelete();

            // Admin who triggered payment (null = auto-paid on instalment confirmation)
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // ── Idempotency constraint ─────────────────────────────────────────────
            // One commission record per instalment × ancestor × level.
            // Inserting the same triple again hits a unique-violation → skip, never duplicate.
            $table->unique(
                ['saving_instalment_id', 'ancestor_id', 'level'],
                'sic_instalment_ancestor_level_unique'
            );

            // ── Performance indexes ───────────────────────────────────────────────
            $table->index('user_id');
            $table->index('ancestor_id');
            $table->index('status');
            $table->index(['instalment_number', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_instalment_commissions');
    }
};
