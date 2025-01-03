<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PVTransaction extends Model
{
   
    protected $fillable = [
        'user_id',
        'transaction_type',
        'reference_id',
        'pv_amount',
        'transaction_type',
        'transaction_date',
        'previous_balance','new_balance','remarks'
    ]; 
}
