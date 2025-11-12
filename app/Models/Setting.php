<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

  
    protected $fillable = [
        'site_name',
        'pv_amount',
        'description',
        'usd','updated_at','activation_code','withdraw_block','registration_fee','blocked_wallets',
        'standard_package_min','standard_package_max','vip_package_min'
    ]; 
}
