<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'transaction_type',
        'level',
        'amount',
        'previous_balance',
        'new_balance',
        'reason',
        'processed_by',
        'reference_number',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'metadata' => 'array'
    ];

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNumber($type = 'RWD')
    {
        $prefix = $type === 'reward_reversed' ? 'RWR' : 'RWD';
        $timestamp = now()->format('ymdHis');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wallet associated with the transaction
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the user who processed the transaction
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for reward reversals
     */
    public function scopeReversals($query)
    {
        return $query->where('transaction_type', 'reward_reversed');
    }

    /**
     * Scope for reward assignments
     */
    public function scopeAssignments($query)
    {
        return $query->where('transaction_type', 'reward_assigned');
    }

    /**
     * Get formatted transaction type
     */
    public function getFormattedTypeAttribute()
    {
        return match($this->transaction_type) {
            'reward_assigned' => 'Reward Assigned',
            'reward_reversed' => 'Reward Reversed',
            default => ucfirst(str_replace('_', ' ', $this->transaction_type))
        };
    }

    /**
     * Get transaction color based on type
     */
    public function getStatusColorAttribute()
    {
        return match($this->transaction_type) {
            'reward_assigned' => 'success',
            'reward_reversed' => 'danger',
            default => 'secondary'
        };
    }
}
