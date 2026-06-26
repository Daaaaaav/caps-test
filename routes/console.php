<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-approve pending bookings when their start time arrives
Schedule::command('bookings:auto-approve')->everyMinute()->withoutOverlapping();

// Auto-complete approved bookings when their end time passes (+1 min tolerance)
Schedule::command('bookings:auto-complete')->everyMinute()->withoutOverlapping();
