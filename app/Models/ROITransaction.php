<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ROITransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'percentage',
        'description',
        'phone_verified',
        'is_active',
        'sponsor_id','ancestor_id','descendant_id','level'
    ]; 

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
