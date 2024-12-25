<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    //

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'phone',
        'date_of_birth',
        'email',
        'address',
        'city',
        'country',
        'postal_code',
        'facebook',
        'state',
        'twitter',
        'instagram',
        'linkedin',
        'github',
        'occupation',
        'bio',
        'skills',

        'account_title',
        'account_number',
        'ibn_number',
        'branch_name',
        'branch_code',
        'bank_name',


    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
