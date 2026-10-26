<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Append-only audit entry for instalment payment updates. */
class InstalmentPaymentAudit extends Model
{
    public const UPDATED_AT = null;

    public const VERIFIED          = 'bank_verified';        // member submission verified + confirmed
    public const RECORDED          = 'bank_recorded';        // office recorded a payment with no submission
    public const REJECTED          = 'bank_not_received';    // submission rejected, money not in bank
    public const REVERSED          = 'reconciliation_reversed';
    public const BLOCKED           = 'verification_blocked'; // duplicate reference, amount mismatch, …
    public const ROLE_ASSIGNED     = 'office_admin_assigned';
    public const ROLE_REMOVED      = 'office_admin_removed';
    public const SHEET_UPLOADED    = 'payment_sheet_uploaded';
    public const SHEET_REJECTED    = 'payment_sheet_rejected';
    public const STAFF_CREATED     = 'staff_account_created';
    public const STAFF_ACTIVATED   = 'staff_account_activated';
    public const STAFF_DEACTIVATED = 'staff_account_deactivated';
    public const STAFF_PASSWORD    = 'staff_password_changed';

    public const LABELS = [
        self::VERIFIED      => 'Bank Payment Verified',
        self::RECORDED      => 'Bank Payment Recorded',
        self::REJECTED      => 'Rejected — Not Received in Bank',
        self::REVERSED      => 'Reconciliation Reversed',
        self::BLOCKED       => 'Verification Blocked',
        self::ROLE_ASSIGNED => 'Staff Role Assigned',
        self::ROLE_REMOVED  => 'Staff Role Removed',
        self::SHEET_UPLOADED => 'Payment Sheet Uploaded',
        self::SHEET_REJECTED => 'Payment Sheet Rejected',
        self::STAFF_CREATED     => 'Staff Account Created',
        self::STAFF_ACTIVATED   => 'Staff Account Activated',
        self::STAFF_DEACTIVATED => 'Staff Account Deactivated',
        self::STAFF_PASSWORD    => 'Staff Password Changed',
    ];

    protected $fillable = [
        'action', 'actor_id', 'user_id', 'saving_instalment_id', 'reconciliation_id',
        'description', 'details', 'ip_address',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Payment audit history is append-only.'));
        static::deleting(fn () => throw new LogicException('Payment audit history is append-only.'));
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function instalment(): BelongsTo
    {
        return $this->belongsTo(SavingInstalment::class, 'saving_instalment_id');
    }

    public function label(): string
    {
        return self::LABELS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}
