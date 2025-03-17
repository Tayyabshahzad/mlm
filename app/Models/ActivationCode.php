<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationCode extends Model
{

    protected $fillable = ['code', 'generated_by', 'status', 'expires_at','updated_at','amount','admin_approval','used_by'];

    public function user()
    {
        return $this->hasOne(User::class, 'activation_code_id');
    }
    
    public function generatedBy(){
        return $this->belongsTo(User::class,'generated_by');
    }

    public function usedBy(){
        return $this->belongsTo(User::class,'used_by');
    }
}
