<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A digital card generated from a member's online wallet.
 *
 * Lifecycle (enforced by DigitalCardService::transition()):
 *
 *   available ──share──▶ shared ──apply to instalment──▶ reserved ──admin confirm──▶ used
 *       │                  │  ▲                           │                           │
 *       │                  │  └──────admin reject─────────┘                           │
 *       │                  │  ▲──────────────────────admin reversal──────────────────┘
 *       ├──expire/cancel──▶ expired / cancelled (refunded to generator)
 *                          └──expire/admin cancel──▶ expired / cancelled
 *
 * Status, ownership and amount always come from this record — never from a
 * downloaded or forwarded image of the card.
 */
class DigitalCard extends Model
{
    use HasUlids;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_SHARED    = 'shared';
    public const STATUS_RESERVED  = 'reserved';
    public const STATUS_USED      = 'used';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_SHARED,
        self::STATUS_RESERVED,
        self::STATUS_USED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    /** Allowed status transitions: from => [to, ...] */
    public const TRANSITIONS = [
        self::STATUS_AVAILABLE => [self::STATUS_SHARED, self::STATUS_EXPIRED, self::STATUS_CANCELLED],
        self::STATUS_SHARED    => [self::STATUS_RESERVED, self::STATUS_EXPIRED, self::STATUS_CANCELLED],
        self::STATUS_RESERVED  => [self::STATUS_USED, self::STATUS_SHARED],
        self::STATUS_USED      => [self::STATUS_SHARED], // only via admin instalment reversal
        self::STATUS_EXPIRED   => [],
        self::STATUS_CANCELLED => [],
    ];

    public const SHARE_PORTAL = 'portal';
    public const SHARE_EMAIL  = 'email';

    protected $fillable = [
        'card_number',
        'pin',
        'amount',
        'status',
        'generated_by',
        'generation_wallet_id',
        'recipient_id',
        'shared_by',
        'shared_at',
        'share_method',
        'saving_instalment_id',
        'reserved_at',
        'used_at',
        'expires_at',
        'expired_at',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'refund_wallet_id',
    ];

    protected $hidden = ['pin'];

    protected $casts = [
        'pin'          => 'encrypted',
        'amount'       => 'decimal:2',
        'shared_at'    => 'datetime',
        'reserved_at'  => 'datetime',
        'used_at'      => 'datetime',
        'expires_at'   => 'datetime',
        'expired_at'   => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /** ULID lives in `reference`; the numeric `id` stays the primary key. */
    public function uniqueIds(): array
    {
        return ['reference'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    // --- Relations ---

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by')->withTrashed();
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id')->withTrashed();
    }

    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by')->withTrashed();
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by')->withTrashed();
    }

    public function instalment(): BelongsTo
    {
        return $this->belongsTo(SavingInstalment::class, 'saving_instalment_id');
    }

    public function generationWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'generation_wallet_id');
    }

    public function refundWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'refund_wallet_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DigitalCardEvent::class)->orderBy('id');
    }

    // --- Helpers ---

    /** Human-readable validity for new cards (Card Settings), e.g. "1 year". */
    public static function validityLabel(): string
    {
        return \App\Support\DigitalCardSettings::validityLabel();
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function isPastExpiry(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Usable for payment right now by its assigned recipient. */
    public function isUsable(): bool
    {
        return $this->status === self::STATUS_SHARED && !$this->isPastExpiry();
    }

    public function formattedNumber(): string
    {
        return trim(chunk_split($this->card_number, 4, ' '));
    }

    public function maskedNumber(): string
    {
        return '•••• •••• •••• ' . substr($this->card_number, -4);
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_SHARED && $this->isPastExpiry()) {
            return 'Expired';
        }

        return match ($this->status) {
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_SHARED    => 'Shared',
            self::STATUS_RESERVED  => 'Pending Review',
            self::STATUS_USED      => 'Used',
            self::STATUS_EXPIRED   => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
            default                => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        if ($this->status === self::STATUS_SHARED && $this->isPastExpiry()) {
            return 'badge-light-danger';
        }

        return match ($this->status) {
            self::STATUS_AVAILABLE => 'badge-light-primary',
            self::STATUS_SHARED    => 'badge-light-info',
            self::STATUS_RESERVED  => 'badge-light-warning',
            self::STATUS_USED      => 'badge-light-success',
            self::STATUS_EXPIRED   => 'badge-light-danger',
            self::STATUS_CANCELLED => 'badge-light-dark',
            default                => 'badge-light',
        };
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(fn ($q) => $q->where('generated_by', $user->id)->orWhere('recipient_id', $user->id));
    }
}
