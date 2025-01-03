<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $fillable = [
        'user_id',
        'from_wallet_type',
        'to_wallet_type',
        'amount',
        'charge',
        'final_amount',
    ];
}
