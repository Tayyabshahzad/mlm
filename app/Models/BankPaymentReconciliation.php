<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A bank transaction verified by an Office Admin and matched to one saving
 * instalment. `reference_key` is unique while the match is active, so the
 * same bank transaction cannot settle two instalments.
 */
class BankPaymentReconciliation extends Model
{
    public const SOURCE_USER_SUBMISSION = 'user_submission';
    public const SOURCE_OFFICE_ENTRY    = 'office_entry';

    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'saving_instalment_id',
        'user_id',
        'bank_reference',
        'reference_key',
        'amount',
        'payment_date',
        'bank_name',
        'source',
        'user_submitted_reference',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'verified_at'  => 'datetime',
        'reversed_at'  => 'datetime',
    ];

    public static function normaliseReference(string $reference): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($reference)));
    }

    public function instalment(): BelongsTo
    {
        return $this->belongsTo(SavingInstalment::class, 'saving_instalment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by')->withTrashed();
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by')->withTrashed();
    }
}
