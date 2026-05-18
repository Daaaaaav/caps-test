<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingRoom;

#[Layout('layouts.superadmin')]
#[Title('Room Booking Statistics')]
class RoomBookingStatistics extends Component
{
    public $viewType = 'monthly';
    public $showList = false;

    public function setViewType($type): void
    {
        $this->viewType = $type;
    }

    public function toggleList(): void
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        try {
            $companyId = Auth::user()->company_id;

            // ── KPI counts (status stored as string or numeric — cover both) ──
            $totalBookings    = BookingRoom::where('company_id', $companyId)->count();
            $pendingBookings  = BookingRoom::where('company_id', $companyId)
                ->whereIn('status', [BookingRoom::ST_PENDING, 'pending', 'PENDING'])->count();
            $approvedBookings = BookingRoom::where('company_id', $companyId)
                ->whereIn('status', [BookingRoom::ST_APPROVED, 'approved', 'APPROVED'])->count();
            $rejectedBookings = BookingRoom::where('company_id', $companyId)
                ->whereIn('status', [BookingRoom::ST_REJECTED, 'rejected', 'REJECTED'])->count();
            $doneBookings     = BookingRoom::where('company_id', $companyId)
                ->whereIn('status', [BookingRoom::ST_DONE, 'done', 'DONE'])->count();

            // ── Chart data ────────────────────────────────────────────────────
            if ($this->viewType === 'monthly') {
                // All 12 months of the current year, zero-filled
                $raw = BookingRoom::where('company_id', $companyId)
                    ->whereYear('created_at', date('Y'))
                    ->selectRaw('MONTH(created_at) as period, COUNT(*) as count')
                    ->groupByRaw('MONTH(created_at)')
                    ->orderByRaw('MONTH(created_at)')
                    ->pluck('count', 'period');

                $months = collect(range(1, 12));
                $labels = $months->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
                $data   = $months->map(fn($m) => (int) ($raw[$m] ?? 0))->toArray();
            } else {
                // Last 7 days, zero-filled for missing dates
                $raw = BookingRoom::where('company_id', $companyId)
                    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                    ->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                    ->groupByRaw('DATE(created_at)')
                    ->orderByRaw('DATE(created_at)')
                    ->pluck('count', 'period');

                $labels = [];
                $data   = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date     = now()->subDays($i)->format('Y-m-d');
                    $labels[] = now()->subDays($i)->format('M d');
                    $data[]   = (int) ($raw[$date] ?? 0);
                }
            }

            $kpis = [
                ['label' => 'Total Bookings', 'value' => $totalBookings,    'color' => 'blue'],
                ['label' => 'Pending',         'value' => $pendingBookings,  'color' => 'yellow'],
                ['label' => 'Approved',        'value' => $approvedBookings, 'color' => 'green'],
                ['label' => 'Rejected',        'value' => $rejectedBookings, 'color' => 'red'],
            ];

            $bookings = $this->showList
                ? BookingRoom::where('company_id', $companyId)
                    ->with(['room', 'user', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            return view('livewire.pages.superadmin.room-booking-statistics', [
                'kpis'     => $kpis,
                'labels'   => $labels,
                'data'     => $data,
                'bookings' => $bookings,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error', title: 'Error',
                message: 'Failed to retrieve room booking data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.room-booking-statistics', [
                'kpis' => [
                    ['label' => 'Total Bookings', 'value' => 0, 'color' => 'blue'],
                    ['label' => 'Pending',         'value' => 0, 'color' => 'yellow'],
                    ['label' => 'Approved',        'value' => 0, 'color' => 'green'],
                    ['label' => 'Rejected',        'value' => 0, 'color' => 'red'],
                ],
                'labels'   => [],
                'data'     => [],
                'bookings' => collect(),
            ]);
        }
    }
}
