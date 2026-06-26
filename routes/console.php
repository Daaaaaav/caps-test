<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Booking Auto-Approve Schedule
|--------------------------------------------------------------------------
| Every minute, promote pending room and vehicle bookings whose start time
| has arrived:
|   - Room bookings:    pending  →  approved    (= "ongoing" in this app)
|   - Vehicle bookings: pending  →  on_progress (= "ongoing" in this app)
*/
Schedule::command('bookings:auto-approve')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
