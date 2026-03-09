<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('leads:update-status')->dailyAt('01:00');
Schedule::command('users:cleanup-unassigned')->dailyAt('02:00');
Schedule::call(function () {
    \App\Models\PicMarketing::query()->update(['weekly_follow_up_count' => 0]);
})->weeklyOn(1, '00:00')->name('reset-weekly-kpi');
Schedule::call(function () {
    \App\Models\PicMarketing::query()->update([
        'up_convert' => 0,
        'down_convert' => 0
    ]);
})->monthly()->name('reset-monthly-kpi');
