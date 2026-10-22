<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Append-only history entry for a digital card. Once written, a row can be
 * neither updated nor deleted through the application.
 */
class DigitalCardEvent extends Model
{
    public const UPDATED_AT = null;

    public const GENERATED           = 'generated';
    public const SHARED              = 'shared';
    public const EMAIL_SENT          = 'email_sent';
    public const DOWNLOADED          = 'downloaded';
    public const PAYMENT_SUBMITTED   = 'payment_submitted';
    public const PAYMENT_CONFIRMED   = 'payment_confirmed';
    public const PAYMENT_REJECTED    = 'payment_rejected';
    public const PAYMENT_REVERSED    = 'payment_reversed';
    public const VERIFICATION_FAILED = 'verification_failed';
    public const EXPIRED             = 'expired';
    public const CANCELLED           = 'cancelled';
    public const REFUNDED            = 'refunded';
    public const SETTINGS_UPDATED    = 'settings_updated'; // not tied to a card
    public const PIN_LOCK_RESET      = 'pin_lock_reset';

    public const LABELS = [
        self::GENERATED           => 'Card Generated',
        self::SHARED              => 'Card Shared',
        self::EMAIL_SENT          => 'Email Sent',
        self::DOWNLOADED          => 'Downloaded',
        self::PAYMENT_SUBMITTED   => 'Used for Instalment (Pending Review)',
        self::PAYMENT_CONFIRMED   => 'Instalment Payment Confirmed — Card Used',
        self::PAYMENT_REJECTED    => 'Instalment Payment Rejected — Card Released',
        self::PAYMENT_REVERSED    => 'Instalment Payment Reversed — Card Restored',
        self::VERIFICATION_FAILED => 'Verification / Payment Attempt Rejected',
        self::EXPIRED             => 'Card Expired',
        self::CANCELLED           => 'Card Cancelled',
        self::REFUNDED            => 'Refunded to Generator',
        self::SETTINGS_UPDATED    => 'Card Settings Updated',
        self::PIN_LOCK_RESET      => 'PIN Lock Reset by Admin',
    ];

    protected $fillable = [
        'digital_card_id',
        'event',
        'actor_id',
        'from_user_id',
        'to_user_id',
        'saving_instalment_id',
        'wallet_id',
        'amount',
        'status_from',
        'status_to',
        'share_method',
        'description',
        'meta',
        'ip_address',
    ];

    protected $casts = [
        'meta'       => 'array',
        'amount'     => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Digital card history is append-only.'));
        static::deleting(fn () => throw new LogicException('Digital card history is append-only.'));
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(DigitalCard::class, 'digital_card_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id')->withTrashed();
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id')->withTrashed();
    }

    public function instalment(): BelongsTo
    {
        return $this->belongsTo(SavingInstalment::class, 'saving_instalment_id');
    }

    public function label(): string
    {
        return self::LABELS[$this->event] ?? ucfirst(str_replace('_', ' ', $this->event));
    }
}
