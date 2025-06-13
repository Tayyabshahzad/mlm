<?php

use App\Console\Commands\GenerateWeeklyROI;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

 
Schedule::command('roi:generate-weekly')
->dailyAt('23:40')
->timezone('Asia/Karachi')
->when(function () {
    return Carbon::now('Asia/Karachi')->dayOfWeek !== Carbon::FRIDAY;
});