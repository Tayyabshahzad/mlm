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
        $setting = Setting::first();
        $response = Http::withOptions([
            'verify' => false, // Disables SSL verification
        ])->get('https://api.currencyfreaks.com/v2.0/rates/latest', [
            'apikey' => '911275d23aa24a51a37d66ed3eae27d2',
        ]);  
        if ($response->successful()) { 
            $data = $response->json(); 
            $usdToPkrRate = $data['rates']['PKR'];    
        }   
        $setting->update([ 
            'usd' => $usdToPkrRate,  
            'updated_at' => Carbon::now()
        ]);    
    }
}
