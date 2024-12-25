<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function online(){ 
        return view('wallets.online'); 
    }

    public function directIndirect(){ 
        return view('wallets.direct-indirect'); 
    }

    public function rewards(){ 
        return view('wallets.reward'); 
    }

    public function ROI(){ 
        return view('wallets.roi'); 
    }

    public function profitShare(){ 
        return view('wallets.profit-share'); 
    }
    public function rank(){ 
        return view('wallets.rank'); 
    }
}
