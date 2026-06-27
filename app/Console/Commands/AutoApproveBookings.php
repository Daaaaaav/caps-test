<?php

namespace App\Console\Commands;

use App\Models\BookingRoom;
use App\Services\GoogleMeetService;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoApproveBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:auto-approve
                            {--dry-run : Preview what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-approve pending room and vehicle bookings whose start time has arrived';

    private string $tz = 'Asia/Jakarta';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tz      = config('app.timezone', $this->tz);
        $now     = Carbon::now($tz);
        $nowStr  = $now->toDateTimeString();
        $isDry   = $this->option('dry-run');

        $this->info('[' . $now->toDateTimeString() . '] Running auto-approve check...');

        // ──────────────────────────────────────────────────────────────────
        // 1. ROOM BOOKINGS — offline
        //    pending → approved when CONCAT(date, ' ', start_time) <= NOW()
        //    Online meetings are handled separately below (need link creation).
        // ──────────────────────────────────────────────────────────────────
        $offlineQuery = DB::table('booking_rooms')
            ->whereNull('deleted_at')
            ->where('status', 'pending')
            ->whereNotIn('booking_type', ['online_meeting', 'onlinemeeting'])
            ->whereNotNull('date')
            ->whereNotNull('start_time')
            ->whereRaw("CONCAT(date, ' ', start_time) <= ?", [$nowStr]);

        $offlineCount = $offlineQuery->count();

        if ($offlineCount > 0) {
            if ($isDry) {
                $this->line("  [DRY-RUN] Would approve {$offlineCount} pending offline room booking(s).");
            } else {
                $affected = DB::table('booking_rooms')
                    ->whereNull('deleted_at')
                    ->where('status', 'pending')
                    ->whereNotIn('booking_type', ['online_meeting', 'onlinemeeting'])
                    ->whereNotNull('date')
                    ->whereNotNull('start_time')
                    ->whereRaw("CONCAT(date, ' ', start_time) <= ?", [$nowStr])
                    ->update([
                        'status'     => 'approved',
                        'is_approve' => 1,
                        'updated_at' => $nowStr,
                    ]);

                $this->info("  ✓ Offline room bookings auto-approved: {$affected}");
            }
        } else {
            $this->line('  Offline room bookings: none to auto-approve.');
        }

        // ──────────────────────────────────────────────────────────────────
        // 2. ONLINE ROOM BOOKINGS
        //    pending → approved when start_time arrives.
        //    Also creates Google Meet / Zoom links if missing.
        // ──────────────────────────────────────────────────────────────────
        $onlinePending = BookingRoom::query()
            ->whereNull('deleted_at')
            ->where('status', 'pending')
            ->whereIn('booking_type', ['online_meeting', 'onlinemeeting'])
            ->whereNotNull('date')
            ->whereNotNull('start_time')
            ->whereRaw("CONCAT(date, ' ', start_time) <= ?", [$nowStr])
            ->get();

        $onlineApproved = 0;

        foreach ($onlinePending as $b) {
            if ($isDry) {
                $this->line("  [DRY-RUN] Would approve online booking #{$b->bookingroom_id} ({$b->meeting_title})");
                continue;
            }

            try {
                DB::transaction(function () use ($b, $nowStr, $tz) {
                    // Create meeting link if not already present
                    if (empty($b->online_meeting_url)) {
                        $start = Carbon::parse($b->start_time, $tz);
                        $end   = Carbon::parse($b->end_time,   $tz);

                        $provider = strtolower(str_replace([' ', '-'], '_', (string) $b->online_provider));
                        $isGoogle = str_starts_with($provider, 'google');

                        if ($isGoogle) {
                            $svc = app(GoogleMeetService::class);
                            if ($svc->isConnected()) {
                                $meet = $svc->createMeet(
                                    $b->meeting_title,
                                    $start,
                                    $end,
                                    'Auto-created by KRBS scheduler'
                                );
                                $b->online_provider         = 'google_meet';
                                $b->online_meeting_url      = $meet['url']      ?? null;
                                $b->online_meeting_code     = $meet['code']     ?? null;
                                $b->online_meeting_password = $meet['password'] ?? null;
                                $b->online_meeting_event_id = $meet['event_id'] ?? null;
                            } else {
                                Log::warning("AutoApprove: Google not connected, skipping link for booking #{$b->bookingroom_id}");
                            }
                        } else {
                            $zoomSvc = app(ZoomService::class);
                            if ($zoomSvc->isConfigured()) {
                                $meet = $zoomSvc->createMeeting(
                                    $b->meeting_title,
                                    $start,
                                    $end,
                                    'Auto-created by KRBS scheduler'
                                );
                                $b->online_provider         = 'zoom';
                                $b->online_meeting_url      = $meet['url']      ?? null;
                                $b->online_meeting_code     = $meet['code']     ?? null;
                                $b->online_meeting_password = $meet['password'] ?? null;
                                $b->online_meeting_event_id = $meet['code']     ?? null;
                            } else {
                                Log::warning("AutoApprove: Zoom not configured, skipping link for booking #{$b->bookingroom_id}");
                            }
                        }
                    }

                    $b->status     = 'approved';
                    $b->is_approve = 1;
                    $b->updated_at = $nowStr;
                    $b->save();
                });

                $onlineApproved++;
            } catch (\Throwable $e) {
                Log::error("AutoApprove: Failed to approve online booking #{$b->bookingroom_id}: " . $e->getMessage());
                $this->error("  ✗ Failed online booking #{$b->bookingroom_id}: " . $e->getMessage());
            }
        }

        if ($onlineApproved > 0) {
            $this->info("  ✓ Online room bookings auto-approved: {$onlineApproved}");
        } else {
            $this->line('  Online room bookings: none to auto-approve.');
        }

        // ──────────────────────────────────────────────────────────────────
        // 3. VEHICLE BOOKINGS — late return detection
        //    approved → late_return  when end_at < NOW()
        // ──────────────────────────────────────────────────────────────────
        $lateQuery = DB::table('vehicle_bookings')
            ->whereNull('deleted_at')
            ->whereIn('status', ['approved', 'on_progress'])
            ->whereNotNull('end_at')
            ->where('end_at', '<', $nowStr);

        $lateCount = $lateQuery->count();

        if ($lateCount > 0) {
            if ($isDry) {
                $this->line("  [DRY-RUN] Would flag {$lateCount} vehicle booking(s) as late_return.");
            } else {
                $affected = DB::table('vehicle_bookings')
                    ->whereNull('deleted_at')
                    ->whereIn('status', ['approved', 'on_progress'])
                    ->whereNotNull('end_at')
                    ->where('end_at', '<', $nowStr)
                    ->update([
                        'status'     => 'late_return',
                        'updated_at' => $nowStr,
                    ]);

                $this->info("  ✓ Vehicle bookings flagged as late_return: {$affected}");
            }
        } else {
            $this->line('  Vehicle bookings: none to flag as late_return.');
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
