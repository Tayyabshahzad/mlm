<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;

class SavingInstalment extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'instalment_number',
        'amount',
        'due_date',
        'status',
        'is_late',
        'deposit_deferred',
        'next_cycle_date',
        'roi_eligible_from',
        'transaction_id',
        'payment_method',
        'submitted_amount',
        'adb_charge',
        'fisp_charge',
        'net_credited',
        'submitted_at',
        'confirmed_at',
        'confirmed_by',
        'deposited_at',
        'notes',
    ];

    protected $casts = [
        'due_date'          => 'date',
        'submitted_at'      => 'datetime',
        'confirmed_at'      => 'datetime',
        'deposited_at'      => 'datetime',
        'next_cycle_date'   => 'date',
        'roi_eligible_from' => 'date',
        'is_late'           => 'boolean',
        'deposit_deferred'  => 'boolean',
        'amount'            => 'decimal:2',
        'submitted_amount'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // --- Scopes ---

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // --- Helpers ---

    public function isDue(): bool
    {
        return Carbon::today()->gte($this->due_date);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && Carbon::today()->gt($this->due_date);
    }

    public function proofScreenshot(): ?string
    {
        $media = $this->getFirstMedia('instalment_proof');
        return $media?->getUrl();
    }
}
