<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('outbox:dispatch')->everyMinute()->withoutOverlapping();

Schedule::command('infaq:send-weekly-report')
    ->weeklyOn(0, '20:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();