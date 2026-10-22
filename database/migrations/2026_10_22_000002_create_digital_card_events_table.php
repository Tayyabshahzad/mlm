<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only audit trail for digital cards. Rows are never updated or
     * deleted by the application (enforced in the DigitalCardEvent model).
     */
    public function up(): void
    {
        Schema::create('digital_card_events', function (Blueprint $table) {
            $table->id();
            // Nullable: failed lookups for a non-existent card number are still logged.
            $table->foreignId('digital_card_id')->nullable()->constrained('digital_cards');
            $table->string('event', 40);
            $table->foreignId('actor_id')->nullable()->constrained('users'); // null = system
            $table->foreignId('from_user_id')->nullable()->constrained('users');
            $table->foreignId('to_user_id')->nullable()->constrained('users');
            $table->unsignedBigInteger('saving_instalment_id')->nullable();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('status_from', 20)->nullable();
            $table->string('status_to', 20)->nullable();
            $table->string('share_method', 10)->nullable();
            $table->string('description', 1000)->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['digital_card_id', 'id']);
            $table->index('event');
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_card_events');
    }
};
