<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;
     
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
        'sponsor_id','ancestor_id','descendant_id','level','last_roi_payment_date','transaction_id','freez_wallet','blocked','usdt_rate','transferred_amount','converted_usdt_amount','fee_deducted','net_invested_usdt_amount','negative_pv','roi_eligible_investment_amount', 'roi_status', 'roi_stopped_at','stop_reason','stop_reason_description','user_plan'

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
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function reflink()
    {
        return $this->hasOne(ReferralLink::class);
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

    public function parent()
    {
        return $this->belongsTo(User::class, 'sponsor_id'); // Adjust 'parent_id' based on your schema
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
