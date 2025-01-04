<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia; 

use Illuminate\Database\Eloquent\Model;
class User extends Authenticatable implements ShouldQueue,HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone_verified',
        'is_active',
        'sponsor_id','ancestor_id','descendant_id','level'
    ]; 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function reflink()
    {
        return $this->hasOne(ReferralLink::class);
    }


    public function team()
    {
        return $this->hasMany(User::class,'sponsor_id');
    }

    public function directDescendants()
    {
        return $this->hasManyThrough(
            User::class,
            User::class,
            'sponsor_id', // Adjust 'parent_id' based on your schema
            'id',
            'id',
            'id'
        );
    }

    public function ancestors()
    {
        return $this->belongsToMany(
            User::class,
            'referral_trees',
            'descendant_id',
            'ancestor_id'
        )->withPivot('level');
    }

    public function descendants()
    {
        return $this->belongsToMany(
            User::class,
            'referral_trees',
            'ancestor_id',
            'descendant_id'
        )->withPivot('level');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'sponsor_id'); // Adjust 'parent_id' based on your schema
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'sponsor_id'); // Adjust 'parent_id' based on your schema
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referrer_user_id');
    }

}
