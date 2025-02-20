<?php

use App\Console\Commands\GenerateWeeklyROI;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();


// Artisan::command('schedule:weekly-roi', function () {
//     $this->call(GenerateWeeklyROI::class);
// });

// Schedule::call(function () {
//    $u = User::where('id',2)->first();
//     $u->delete();
// })->daily();

Schedule::command('roi:generate-weekly')
    ->dailyAt('23:59') // Runs at 11:59 PM
    ->when(fn () => now()->dayOfWeek !== Carbon::FRIDAY) // Skips Friday
    ->appendOutputTo(storage_path('logs/roi_generation.log')); // Log output

