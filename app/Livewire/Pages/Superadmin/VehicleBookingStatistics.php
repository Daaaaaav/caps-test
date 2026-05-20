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
    public string $timeRange = '90days';
    public bool   $showList  = false;

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
    }

    public function toggleList(): void
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        try {
            $companyId = Auth::user()->company_id;

            $days = match($this->timeRange) {
                '30days' => 30,
                '90days' => 90,
                default  => 7,
            };

            $since = now()->subDays($days)->startOfDay();

            // ── KPI counts ────────────────────────────────────────────────────
            // vehicle_bookings statuses: pending | approved | on_progress | completed | cancelled | rejected | returned
            $totalBookings      = VehicleBooking::where('company_id', $companyId)->where('created_at', '>=', $since)->count();
            $pendingBookings    = VehicleBooking::where('company_id', $companyId)->where('created_at', '>=', $since)->where('status', 'pending')->count();
            $approvedBookings   = VehicleBooking::where('company_id', $companyId)->where('created_at', '>=', $since)->where('status', 'approved')->count();
            $onProgressBookings = VehicleBooking::where('company_id', $companyId)->where('created_at', '>=', $since)->where('status', 'on_progress')->count();
            $completedBookings  = VehicleBooking::where('company_id', $companyId)->where('created_at', '>=', $since)->whereIn('status', ['completed', 'returned'])->count();
            $rejectedBookings   = VehicleBooking::where('company_id', $companyId)->where('created_at', '>=', $since)->whereIn('status', ['rejected', 'cancelled'])->count();

            // ── Daily chart — zero-filled for every day in range ──────────────
            $raw = VehicleBooking::where('company_id', $companyId)
                ->where('created_at', '>=', $since)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->pluck('count', 'date');

            $labels = [];
            $data   = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date     = now()->subDays($i)->format('Y-m-d');
                $labels[] = now()->subDays($i)->format('M d');
                $data[]   = (int) ($raw[$date] ?? 0);
            }

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
                    ->where('created_at', '>=', $since)
                    ->with(['vehicle', 'user', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            $this->dispatch('vehicle-chart-updated', labels: $labels, data: $data);

            return view('livewire.pages.superadmin.vehicle-booking-statistics', [
                'kpis'     => $kpis,
                'labels'   => $labels,
                'data'     => $data,
                'bookings' => $bookings,
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
                'labels'   => [],
                'data'     => [],
                'bookings' => collect(),
            ]);
        }
    }
}
