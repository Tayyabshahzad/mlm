<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithDrawalequest extends Model
{
    protected $fillable = [
        'user_id', 
        'amount',
        'status',
        'target_account_details',
        'review_notes',
        'profile_id'
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
