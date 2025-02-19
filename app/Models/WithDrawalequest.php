<?php

namespace App\Models;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia; 
use Illuminate\Database\Eloquent\Model; 
class WithDrawalequest extends Model implements HasMedia
{
    use  InteractsWithMedia;
    protected $fillable = [
        'user_id', 
        'amount',
        'status',
        'target_account_details',
        'review_notes',
        'profile_id','request_type','transfer_fee'
    ];

    protected $casts = [
        'target_account_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
