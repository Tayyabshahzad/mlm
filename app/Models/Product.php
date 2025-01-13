<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia; 
class Product extends Model implements HasMedia
{
    use  InteractsWithMedia;
    protected $fillable = ['name', 'price' , 'description'];
}
