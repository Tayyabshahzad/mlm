<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvestment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'investment_from',
        'committed_amount',
        'total_earnings',
        'roi_status',
        'completed_at',
        'user_plan_at_time'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'committed_amount' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'investment_from');
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if this investment has reached 2x
     */
    public function hasReached2X(): bool
    {
        return $this->total_earnings >= $this->committed_amount;
    }

    /**
     * Get remaining amount before 2x
     */
    public function getRemainingTo2X(): float
    {
        return max(0, $this->committed_amount - $this->total_earnings);
    }

    /**
     * Scope for active investments only
     */
    public function scopeActive($query)
    {
        return $query->where('roi_status', 'active');
    }

    /**
     * Scope for completed investments
     */
    public function scopeCompleted($query)
    {
        return $query->where('roi_status', 'completed');
    }
}
