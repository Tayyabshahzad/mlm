<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvestment extends Model
{
    protected $fillable = [
        'user_id', 
        'amount',
        'type', 
        'description','investment_from'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'investment_from');
    }
}
