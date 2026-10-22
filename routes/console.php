<?php

use App\Console\Commands\GenerateWeeklyROI;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

 
// Standard investment ROI — daily at 23:40, skip Fridays
Schedule::command('roi:generate-weekly')
    ->dailyAt('23:40')
    ->timezone('Asia/Karachi')
    ->when(function () {
        return Carbon::now('Asia/Karachi')->dayOfWeek !== Carbon::FRIDAY;
    });

// Saving account ROI (new saving users + enrolled standard users) — daily at 23:59, skip Fridays
Schedule::command('roi:process-saving')
    ->dailyAt('23:59')
    ->timezone('Asia/Karachi')
    ->when(function () {
        return Carbon::now('Asia/Karachi')->dayOfWeek !== Carbon::FRIDAY;
    });
// Digital cards — expire unused cards past their expiry date and refund generators
Schedule::command('cards:expire')
    ->hourly()
    ->timezone('Asia/Karachi')
    ->withoutOverlapping();
