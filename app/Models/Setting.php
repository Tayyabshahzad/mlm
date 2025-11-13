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
        'standard_package_min','standard_package_max','vip_package_min',
        'standard_profit_multiplier','vip_profit_multiplier',
        'standard_commission_l1','standard_commission_l2','standard_commission_l3','standard_commission_l4',
        'standard_commission_l5','standard_commission_l6','standard_commission_l7',
        'vip_commission_l1','vip_commission_l2','vip_commission_l3','vip_commission_l4',
        'vip_commission_l5','vip_commission_l6','vip_commission_l7'
    ]; 
}
