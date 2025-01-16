<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_type',
        'balance',
        'direct_balance',
        'indirect_balance',
        'wallet_from',
        'commission_type',
        'level','percentage','total_amount'
    ];
    public function user()
    {
        return $this->belongsTo(User::class); // Assuming your User model exists and has a relationship
    }

    public function form()
    {
        return $this->belongsTo(User::class,'wallet_from'); // Assuming your User model exists and has a relationship
    }
}
