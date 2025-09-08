<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingReward extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'reward_amount',
        'team_count',
        'users_required',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'eligibility_data',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'eligibility_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDenied($query)
    {
        return $query->where('status', 'denied');
    }
}
