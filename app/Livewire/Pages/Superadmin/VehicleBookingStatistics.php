<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\VehicleBooking;

#[Layout('layouts.superadmin')]
#[Title('Vehicle Booking Statistics')]
class VehicleBookingStatistics extends Component
{
    public string $chartType   = 'line';
    public bool   $showList    = false;
    public int    $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = (int) date('Y');
    }

    public function setChartType(string $type): void
    {
        $this->chartType = $type;
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
            // vehicle_bookings statuses: pending | approved | on_progress | completed | cancelled | rejected | returned
            $totalBookings     = VehicleBooking::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd])->count();
            $pendingBookings   = VehicleBooking::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd])->where('status', 'pending')->count();
            $approvedBookings  = VehicleBooking::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd])->where('status', 'approved')->count();
            $onProgressBookings = VehicleBooking::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd])->where('status', 'on_progress')->count();
            $completedBookings = VehicleBooking::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd])->whereIn('status', ['completed', 'returned'])->count();
            $rejectedBookings  = VehicleBooking::where('company_id', $companyId)->whereBetween('created_at', [$yearStart, $yearEnd])->whereIn('status', ['rejected', 'cancelled'])->count();

            // ── Available years for selector ──────────────────────────────────
            $availableYears = VehicleBooking::where('company_id', $companyId)
                ->selectRaw('YEAR(created_at) as y')
                ->groupByRaw('YEAR(created_at)')
                ->orderByRaw('YEAR(created_at)')
                ->pluck('y')
                ->map(fn($y) => (int) $y)
                ->toArray();

            if (empty($availableYears)) {
                $availableYears = [(int) date('Y')];
            }

            // ── Monthly chart — all 12 months, zero-filled ────────────────────
            $raw = VehicleBooking::where('company_id', $companyId)
                ->whereYear('created_at', $this->selectedYear)
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(created_at)')
                ->orderByRaw('MONTH(created_at)')
                ->pluck('count', 'month');

            $months = collect(range(1, 12));
            $labels = $months->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
            $data   = $months->map(fn($m) => (int) ($raw[$m] ?? 0))->toArray();

            $kpis = [
                ['label' => 'Total Bookings', 'value' => $totalBookings,      'color' => 'blue',   'icon' => 'truck'],
                ['label' => 'Pending',         'value' => $pendingBookings,    'color' => 'yellow', 'icon' => 'clock'],
                ['label' => 'Approved',        'value' => $approvedBookings,   'color' => 'green',  'icon' => 'check-circle'],
                ['label' => 'In Progress',     'value' => $onProgressBookings, 'color' => 'purple', 'icon' => 'arrow-path'],
                ['label' => 'Completed',       'value' => $completedBookings,  'color' => 'gray',   'icon' => 'check-badge'],
                ['label' => 'Rejected',        'value' => $rejectedBookings,   'color' => 'red',    'icon' => 'x-circle'],
            ];

            $bookings = $this->showList
                ? VehicleBooking::where('company_id', $companyId)
                    ->whereBetween('created_at', [$yearStart, $yearEnd])
                    ->with(['vehicle', 'user', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            $this->dispatch('vehicle-chart-updated', labels: $labels, data: $data, chartType: $this->chartType);

            return view('livewire.pages.superadmin.vehicle-booking-statistics', [
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
                message: 'Failed to retrieve vehicle booking data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.vehicle-booking-statistics', [
                'kpis' => [
                    ['label' => 'Total Bookings', 'value' => 0, 'color' => 'blue',   'icon' => 'truck'],
                    ['label' => 'Pending',         'value' => 0, 'color' => 'yellow', 'icon' => 'clock'],
                    ['label' => 'Approved',        'value' => 0, 'color' => 'green',  'icon' => 'check-circle'],
                    ['label' => 'In Progress',     'value' => 0, 'color' => 'purple', 'icon' => 'arrow-path'],
                    ['label' => 'Completed',       'value' => 0, 'color' => 'gray',   'icon' => 'check-badge'],
                    ['label' => 'Rejected',        'value' => 0, 'color' => 'red',    'icon' => 'x-circle'],
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
