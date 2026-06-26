<?php

namespace App\Console\Commands;

use App\Models\BookingRoom;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCompleteBookings extends Command
{
    protected $signature = 'bookings:auto-complete';
    protected $description = 'Auto-complete approved bookings whose end time has passed (with 1 minute tolerance)';

    private string $tz = 'Asia/Jakarta';

    public function handle(): int
    {
        $now = Carbon::now($this->tz);
        // Add 1 minute tolerance: booking ending at 11:00 completes at 11:01
        // This allows consecutive bookings (e.g., 09:00-11:00 and 11:00-13:00)
        $threshold = $now->copy()->subMinute()->format('Y-m-d H:i:s');

        $endExpr = "COALESCE(
            CASE WHEN end_time REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} ' THEN end_time END,
            CASE WHEN date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} ' THEN date END,
            CONCAT(date, ' ', end_time)
        )";

        $updated = DB::transaction(function () use ($threshold, $endExpr) {
            return BookingRoom::query()
                ->whereNotNull('date')
                ->whereNotNull('end_time')
                ->whereRaw("$endExpr IS NOT NULL")
                ->whereRaw("$endExpr <= ?", [$threshold])
                ->where(function ($q) {
                    $q->whereRaw("LOWER(TRIM(`status`)) = 'approved'");
                })
                ->update([
                    'status'     => 'completed',
                    'updated_at' => Carbon::now($this->tz)->toDateTimeString(),
                ]);
        });

        $this->info("Auto-completed {$updated} booking(s).");
        return self::SUCCESS;
    }
}
