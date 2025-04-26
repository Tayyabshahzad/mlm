<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentSlab extends Model
{

    protected $fillable = [
        'user_id', 
        'slab_count',
        'amount',
        'achived_at', 
        'will_pay_at','status','maturity_date','current_balance'
    ];


}
