<?php

use App\Console\Commands\GenerateWeeklyROI;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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



// Schedule::command('roi:generate-weekly')
// ->dailyAt('23:59')
// ->when(function () {
//     return now()->dayOfWeek !== \Carbon\Carbon::FRIDAY;
// });

Schedule::command('app:update-setting')->twiceDaily(10, 22);


Schedule::command('roi:generate-weekly')
->dailyAt('23:59')
->when(function () {
    $isFriday = now()->dayOfWeek === Carbon::FRIDAY;

    if ($isFriday) {
        Log::info('Skipped roi:generate-weekly command on Friday: ' . now());
    } else {
        Log::info('Executing roi:generate-weekly command on: ' . now());
    }

    return !$isFriday; // Run only if today is NOT Friday
})
->onSuccess(function () {
    Log::info('roi:generate-weekly command executed successfully on: ' . now());
})
->onFailure(function () {
    Log::error('roi:generate-weekly command failed on: ' . now());
});