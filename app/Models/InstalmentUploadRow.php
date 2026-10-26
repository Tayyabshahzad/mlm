<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One member payment from an accepted payment sheet. */
class InstalmentUploadRow extends Model
{
    protected $fillable = ['batch_id', 'row_number', 'user_id', 'saving_instalment_id', 'username', 'email', 'txn_id', 'amount', 'rejected_reason', 'rejected_at'];

    protected $casts = ['amount' => 'decimal:2', 'rejected_at' => 'datetime'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InstalmentUploadBatch::class, 'batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function instalment(): BelongsTo
    {
        return $this->belongsTo(SavingInstalment::class, 'saving_instalment_id');
    }

    /** Transaction id this row put on the instalment (generated when the sheet left it blank). */
    public function expectedTxn(): string
    {
        return $this->txn_id ?? "SHEET-{$this->batch_id}-{$this->row_number}";
    }

    /**
     * What happened to this payment: confirmed | awaiting | rejected.
     * It only counts as this sheet's payment while the instalment still carries
     * the sheet's transaction id (an admin reject clears it).
     */
    public function processingStatus(): string
    {
        $inst = $this->instalment;
        if (!$inst || $inst->transaction_id !== $this->expectedTxn()) {
            return 'rejected';
        }

        return match ($inst->status) {
            'confirmed' => 'confirmed',
            'submitted' => 'awaiting',
            default     => 'rejected',
        };
    }

    /** SQL equivalent of expectedTxn(), for aggregate queries (rows aliased r). */
    public const EXPECTED_TXN_SQL = "COALESCE(r.txn_id, CONCAT('SHEET-', r.batch_id, '-', r.row_number))";
}
