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
    public string $viewType    = 'monthly';
    public bool   $showList    = false;
    public int    $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');
    }

    public function setViewType(string $type): void
    {
        $this->viewType = $type;
    }

    public function setYear(int $year): void
    {
        $this->selectedYear = $year;
    }

    public function toggleList(): void
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        try {
            $companyId = Auth::user()->company_id;

            $yearStart = "{$this->selectedYear}-01-01";
            $yearEnd   = "{$this->selectedYear}-12-31 23:59:59";

            // ── KPI counts scoped to selected year ────────────────────────────
            // booking_rooms only stores string statuses: 'pending', 'approved', 'rejected', 'completed'
            $base = BookingRoom::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd]);

            $totalBookings     = (clone $base)->count();
            $pendingBookings   = (clone $base)->where('status', 'pending')->count();
            $approvedBookings  = (clone $base)->where('status', 'approved')->count();
            $rejectedBookings  = (clone $base)->where('status', 'rejected')->count();
            $completedBookings = (clone $base)->whereIn('status', ['completed', 'done'])->count();

            // ── Available years for selector ──────────────────────────────────
            $availableYears = BookingRoom::where('company_id', $companyId)
                ->selectRaw('YEAR(created_at) as y')
                ->groupByRaw('YEAR(created_at)')
                ->orderByRaw('YEAR(created_at)')
                ->pluck('y')
                ->map(fn($y) => (int) $y)
                ->toArray();

            if (empty($availableYears)) {
                $availableYears = [(int) date('Y')];
            }

            // ── Chart data ────────────────────────────────────────────────────
            if ($this->viewType === 'monthly') {
                $raw = BookingRoom::where('company_id', $companyId)
                    ->whereYear('created_at', $this->selectedYear)
                    ->selectRaw('MONTH(created_at) as period, COUNT(*) as count')
                    ->groupByRaw('MONTH(created_at)')
                    ->orderByRaw('MONTH(created_at)')
                    ->pluck('count', 'period');

                $months = collect(range(1, 12));
                $labels = $months->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
                $data   = $months->map(fn($m) => (int) ($raw[$m] ?? 0))->toArray();
            } else {
                // Daily: last 7 days (not year-filtered — always shows recent activity)
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
                ['label' => 'Total Bookings', 'value' => $totalBookings,     'color' => 'blue'],
                ['label' => 'Pending',         'value' => $pendingBookings,   'color' => 'yellow'],
                ['label' => 'Approved',        'value' => $approvedBookings,  'color' => 'green'],
                ['label' => 'Rejected',        'value' => $rejectedBookings,  'color' => 'red'],
                ['label' => 'Completed',       'value' => $completedBookings, 'color' => 'gray'],
            ];

            $bookings = $this->showList
                ? BookingRoom::where('company_id', $companyId)
                    ->whereBetween('created_at', [$yearStart, $yearEnd])
                    ->with(['room', 'user', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            $this->dispatch('room-chart-updated', labels: $labels, data: $data);

            return view('livewire.pages.superadmin.room-booking-statistics', [
                'kpis'           => $kpis,
                'labels'         => $labels,
                'data'           => $data,
                'bookings'       => $bookings,
                'selectedYear'   => $this->selectedYear,
                'availableYears' => $availableYears,
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
                    ['label' => 'Completed',       'value' => 0, 'color' => 'gray'],
                ],
                'labels'         => [],
                'data'           => [],
                'bookings'       => collect(),
                'selectedYear'   => $this->selectedYear,
                'availableYears' => [(int) date('Y')],
            ]);
        }
    }
}
