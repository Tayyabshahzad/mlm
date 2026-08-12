<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingInstalmentCommissionConfig extends Model
{
    protected $fillable = ['instalment_number', 'level', 'percentage'];

    protected $casts = ['percentage' => 'float'];
}
