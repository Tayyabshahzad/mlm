<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_cards', function (Blueprint $table) {
            $table->id();
            // Public, unguessable identifier used in URLs (never the numeric id).
            $table->ulid('reference')->unique();
            // 16-digit Luhn-valid card number printed on the card.
            $table->string('card_number', 16)->unique();
            // 4-digit PIN, stored encrypted (model cast).
            $table->text('pin');
            $table->decimal('amount', 12, 2);

            // available | shared | reserved | used | expired | cancelled
            $table->string('status', 20)->default('available');

            $table->foreignId('generated_by')->constrained('users');
            $table->unsignedBigInteger('generation_wallet_id')->nullable(); // online-wallet debit row

            $table->foreignId('recipient_id')->nullable()->constrained('users');
            $table->foreignId('shared_by')->nullable()->constrained('users');
            $table->timestamp('shared_at')->nullable();
            $table->string('share_method', 10)->nullable(); // portal | email

            // Instalment the card is reserved for / was used on.
            $table->foreignId('saving_instalment_id')->nullable()->constrained('saving_instalments')->nullOnDelete();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('used_at')->nullable();

            $table->timestamp('expires_at');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->string('cancel_reason', 500)->nullable();
            $table->unsignedBigInteger('refund_wallet_id')->nullable(); // online-wallet credit row

            $table->timestamps();

            $table->index('status');
            $table->index(['recipient_id', 'status']);
            $table->index(['generated_by', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_cards');
    }
};
