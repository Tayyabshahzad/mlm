<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements ShouldQueue,HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia, SoftDeletes;
     
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone_verified',
        'is_active',
        'phone_number',
        'payment_method',
        'agreement_sent',
        'agreement_balance',
        'agreement_balance_locked',
        'current_rank_level',
        'eligible_for_binary_2x',
        'eligible_for_binary_7x',
        'total_binary_earnings',
        'can_login',
        'sponsor_id','ancestor_id','descendant_id','level','last_roi_payment_date','transaction_id','freez_wallet','blocked','usdt_rate','transferred_amount','converted_usdt_amount','fee_deducted','net_invested_usdt_amount','negative_pv','roi_eligible_investment_amount', 'roi_status', 'roi_stopped_at','stop_reason','stop_reason_description','user_plan',
        'cnic',
        'kyc_status', 'kyc_rejection_reason', 'kyc_submitted_at', 'kyc_reviewed_at', 'kyc_reviewed_by',
        'account_type','saving_plan_start_date','saving_registration_completed','saving_total_deposited','saving_initial_payment','saving_initial_fee','adb_option','fisp_option','saving_enrolled',
        'saving_enrollment_activated','saving_enrollment_activated_at','saving_enrollment_activated_by',
        'last_saving_roi_payment_date'

    ]; 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function delete(): bool|null
    {
        if ($this->isForceDeleting()) {
            return parent::delete();
        }

        // Free everything that must be unique (email, username, CNIC, referral link)
        // before soft-deleting, so the same person can register again.
        // The old values stay readable with a "deleted_" / "del<id>-" prefix.
        $suffix = $this->id . '_' . time();
        $this->email    = 'deleted_' . $suffix . '@deleted.invalid';
        $this->username = 'deleted_' . $suffix;
        if ($this->cnic && !str_starts_with($this->cnic, 'del')) {
            $this->cnic = 'del' . $this->id . '-' . $this->cnic;
        }
        $this->save();

        \Illuminate\Support\Facades\DB::table('referral_links')
            ->where('user_id', $this->id)->where('link', 'not like', 'del%')
            ->update(['link' => \Illuminate\Support\Facades\DB::raw("CONCAT('del', user_id, '-', link)"), 'is_active' => false]);

        return parent::delete();
    }

    /**
     * Why this account can NOT be removed permanently (empty = safe to remove).
     * Removing a user cascades in the database: their whole downline (users.sponsor_id)
     * and commissions they generated for others (wallets.wallet_from) would go too,
     * so only accounts nobody else depends on may be removed for good.
     */
    public function permanentDeleteBlockers(): array
    {
        $db = \Illuminate\Support\Facades\DB::class;
        $id = $this->id;
        $checks = [
            'has members under them'                => fn () => $db::table('users')->where('sponsor_id', $id)->exists()
                                                             || $db::table('referral_trees')->where('ancestor_id', $id)->exists(),
            'gave commission/profit to other users'  => fn () => $db::table('wallets')->where('wallet_from', $id)->where('user_id', '!=', $id)->exists()
                                                             || $db::table('commission_logs')->where('source_user_id', $id)->where('user_id', '!=', $id)->exists()
                                                             || $db::table('saving_instalment_commissions')->where('user_id', $id)->exists(),
            'has approved withdrawals'              => fn () => $db::table('with_drawalequests')->where('user_id', $id)->where('status', 'approved')->exists(),
            'has member transfers'                  => fn () => $db::table('wallets')->where('user_id', $id)->where('commission_type', 'Member Transfer')->exists(),
            'is linked to digital cards'            => fn () => $db::table('digital_cards')->where(fn ($q) => $q->where('generated_by', $id)->orWhere('recipient_id', $id)->orWhere('shared_by', $id)->orWhere('cancelled_by', $id))->exists()
                                                             || $db::table('digital_card_events')->where(fn ($q) => $q->where('actor_id', $id)->orWhere('from_user_id', $id)->orWhere('to_user_id', $id))->exists(),
            'is linked to bank / payment sheet records' => fn () => $db::table('bank_payment_reconciliations')->where(fn ($q) => $q->where('user_id', $id)->orWhere('verified_by', $id)->orWhere('reversed_by', $id))->exists()
                                                             || $db::table('instalment_payment_audits')->where('actor_id', $id)->exists()
                                                             || $db::table('instalment_upload_rows')->where('user_id', $id)->exists()
                                                             || $db::table('instalment_upload_batches')->where('uploaded_by', $id)->exists(),
            'created activation codes'              => fn () => $db::table('activation_codes')->where('generated_by', $id)->exists(),
            'is a staff / admin account'            => fn () => $this->roles()->whereIn('name', ['admin', 'super-admin', 'operator', 'office-admin'])->exists(),
        ];

        return array_keys(array_filter($checks, fn ($check) => $check()));
    }

    /** Remove the account and all of its own data for good (only when nothing blocks it). */
    public function deletePermanently(): void
    {
        if ($blockers = $this->permanentDeleteBlockers()) {
            throw new \RuntimeException('Cannot delete permanently: ' . implode(', ', $blockers));
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->roles()->detach();
            $this->permissions()->detach();
            \Illuminate\Support\Facades\DB::table('notifications')->where('notifiable_type', static::class)->where('notifiable_id', $this->id)->delete();
            \Illuminate\Support\Facades\DB::table('transaction_logs')->where('user_id', $this->id)->delete();
            // own wallets, profile, referral link, tree rows, instalments, investments … go by database cascade
            $this->forceDelete();
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('kyc_cnic_front')->singleFile();
        $this->addMediaCollection('kyc_cnic_back')->singleFile();
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function reflink()
    {
        return $this->hasOne(ReferralLink::class);
    }


    public function savingInstalments()
    {
        return $this->hasMany(\App\Models\SavingInstalment::class);
    }

    /**
     * Staff-only Data Entry Operator: has the 'operator' role and no member,
     * admin or office-admin role. These accounts may only use Payment Sheets.
     */
    public function isDataEntryOperatorOnly(): bool
    {
        return $this->hasRole('operator') && !$this->hasAnyRole(['admin', 'member', 'office-admin']);
    }

    /** Staff account (created from Staff Roles) that no longer holds any role that grants access. */
    public function isStaffWithoutAccess(): bool
    {
        return (bool) $this->is_staff_account
            && !$this->hasAnyRole(['admin', 'member', 'office-admin', 'operator']);
    }

    public function isSavingAccount(): bool
    {
        return $this->account_type === 'saving';
    }

    /**
     * Whether this user is allowed to see/copy their saving referral link.
     * Admins always can. Regular users only can if they are registered in the
     * saving plan (new saving account OR enrolled standard user) AND their
     * registration is fully completed.
     */
    public function canSeeSavingLink(): bool
    {
        if ($this->hasRole('admin') || $this->hasRole('super-admin')) {
            return true;
        }

        return $this->saving_registration_completed &&
               ($this->account_type === 'saving' || $this->saving_enrolled);
    }

    public function team()
    {
        return $this->hasMany(User::class,'sponsor_id');
    }

    public function referralTrees()
    {
        return $this->hasMany(ReferralTree::class,'sponsor_id');
    }

    public function directTeam()
    {
        return $this->hasMany(User::class,'sponsor_id');
    }



    public function directDescendants()
    {
        return $this->hasManyThrough(
            User::class,
            User::class,
            'sponsor_id', // Adjust 'parent_id' based on your schema
            'id',
            'id',
            'id'
        );
    }

    public function ancestors()
    {
        return $this->belongsToMany(
            User::class,
            'referral_trees',
            'descendant_id',
            'ancestor_id'
        )->withPivot('level');
    }

    public function descendants()
    {
        return $this->belongsToMany(
            User::class,
            'referral_trees',
            'ancestor_id',
            'descendant_id'
        )->withPivot('level');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'sponsor_id'); // Adjust 'parent_id' based on your schema
    }

    /**
     * True when $other is in this member's own line: anywhere in their upline
     * or anywhere in their downline, in the standard OR saving tree.
     * Members in other teams (left/right, cousins) are not in the line.
     */
    public function isInMyLine(User $other): bool
    {
        if ($other->id === $this->id) {
            return false;
        }
        if ($other->id === $this->sponsor_id || $other->sponsor_id === $this->id) {
            return true;
        }

        return \Illuminate\Support\Facades\DB::table('referral_trees')
            ->where(fn ($q) => $q
                ->where(fn ($d) => $d->where('ancestor_id', $this->id)->where('descendant_id', $other->id))   // downline
                ->orWhere(fn ($u) => $u->where('ancestor_id', $other->id)->where('descendant_id', $this->id))) // upline
            ->exists();
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    /**
     * Direct sponsor in the SAVING tree (level-1 ancestor in referral_trees where tree_type = 'saving').
     * Different from sponsor_id which tracks the standard investment tree.
     */
    public function savingSponsor(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'referral_trees',
            'descendant_id',
            'ancestor_id'
        )->wherePivot('tree_type', 'saving')
         ->wherePivot('level', 1);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referrer_user_id');
    }

    public function activationCode()
    {
        return $this->belongsTo(ActivationCode::class, 'activation_code_id');
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function binarySystems()
    {
        return $this->hasMany(BinarySystem::class);
    }

    public function binary2x()
    {
        return $this->hasOne(BinarySystem::class)->where('system_type', '2x')->where('is_active', true);
    }

    public function binary7x()
    {
        return $this->hasOne(BinarySystem::class)->where('system_type', '7x')->where('is_active', true);
    }

    public function currentRank()
    {
        return $this->hasOne(UserRank::class)->where('is_active', true);
    }

    public function rankHistory()
    {
        return $this->hasMany(UserRank::class)->orderBy('achieved_at', 'desc');
    }

    public function canAccessBinary($type)
    {
        if ($type === '2x') {
            return $this->eligible_for_binary_2x;
        }
        if ($type === '7x') {
            return $this->eligible_for_binary_7x;
        }
        return false;
    }

    public function updateAgreementBalance()
    {
        if ($this->agreement_balance_locked) {
            $totalInvestment = $this->wallets()
                ->where('wallet_type', 'investment')
                ->sum('total_amount');

            $this->update(['agreement_balance' => $totalInvestment]);
        }
    }

}
