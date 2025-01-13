<?php

use App\Console\Commands\GenerateWeeklyROI;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('schedule:weekly-roi', function () {
    $this->call(GenerateWeeklyROI::class);
});

Schedule::call(function () {
   $u = User::where('id',2)->first();
    $u->delete();
})->daily();