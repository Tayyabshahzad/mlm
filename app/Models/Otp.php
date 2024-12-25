<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['phone_number', 'otp', 'expires_at', 'is_verified', 'user_id','last_attempt_at'];
}
