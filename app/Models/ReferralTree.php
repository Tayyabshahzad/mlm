<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralTree extends Model
{
    protected $fillable = ['ancestor_id', 'descendant_id', 'level'];

    public function ancestor()
    {
        return $this->belongsTo(User::class, 'ancestor_id');
    }

    public function descendant()
    {
        return $this->belongsTo(User::class, 'descendant_id');
    }
}
