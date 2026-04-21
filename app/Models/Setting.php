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
        'vip_commission_l5','vip_commission_l6','vip_commission_l7',
        'standard_profit_l1','standard_profit_l2','standard_profit_l3','standard_profit_l4',
        'standard_profit_l5','standard_profit_l6','standard_profit_l7',
        'vip_profit_l1','vip_profit_l2','vip_profit_l3','vip_profit_l4',
        'vip_profit_l5','vip_profit_l6','vip_profit_l7',
        'min_withdrawal_limit','min_member_transfer','min_wallet_transfer',
        'bank_withdrawal_fee_percent','cash_withdrawal_fee_percent','usdt_withdrawal_discount_percent',
        'saving_parent_user_id','saving_registration_fee','saving_min_deposit',
        'saving_monthly_instalment','saving_plan_months',
        'saving_commission_l1','saving_commission_l2','saving_commission_l3','saving_commission_l4',
        'saving_commission_l5','saving_commission_l6','saving_commission_l7',
        'saving_roi_daily_rate',
        'saving_commission_min_transfer'
    ];
}
