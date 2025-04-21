<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateSetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-setting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The Setting Data Will Update';

    /**
     * Execute the console command.
     */ 

    public function handle()
    {
        try {
            $setting = Setting::first(); 
            $response = Http::withOptions([
                'verify' => false,
            ])->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'tether',
                'vs_currencies' => 'pkr',
            ]); 

            if ($response->successful()) {
                $data = $response->json();
                $usdtToPkrRate = $data['tether']['pkr']; 
                $setting->update([
                    'usd' => $usdtToPkrRate,
                    'updated_at' => Carbon::now()
                ]);
                $this->info('USDT rate updated to ' . $usdtToPkrRate);
            } else {
                $this->error('CoinGecko API failed. Keeping existing USDT rate.');
            }
        } catch (\Exception $e) {
            $this->error('Error in USDT rate update: ' . $e->getMessage());
        }
    }



    
}