<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralTree extends Model
{
    protected $fillable = ['user_id', 'parent_id', 'level']; 
}
