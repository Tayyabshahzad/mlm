<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingInstalmentCommission extends Model
{
    protected $fillable = [
        'saving_instalment_id',
        'user_id',
        'ancestor_id',
        'level',
        'instalment_number',
        'commission_amount',
        'percentage',
        'commission_type',
        'status',
        'wallet_id',
        'processed_by',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:4',
        'percentage'        => 'decimal:4',
        'processed_at'      => 'datetime',
        'level'             => 'integer',
        'instalment_number' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function instalment(): BelongsTo
    {
        return $this->belongsTo(SavingInstalment::class, 'saving_instalment_id');
    }

    /** The member whose instalment generated this commission. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The upline who receives the commission. */
    public function ancestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ancestor_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
