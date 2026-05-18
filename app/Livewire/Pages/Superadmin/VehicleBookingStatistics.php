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
    public $chartType = 'line';
    public $showList  = false;

    public function setChartType($type): void
    {
        $this->chartType = $type;
    }

    public function toggleList(): void
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        try {
            $companyId = Auth::user()->company_id;

            // ── KPI counts ────────────────────────────────────────────────────
            $totalBookings     = VehicleBooking::where('company_id', $companyId)->count();
            $pendingBookings   = VehicleBooking::where('company_id', $companyId)->where('status', 'pending')->count();
            $approvedBookings  = VehicleBooking::where('company_id', $companyId)->where('status', 'approved')->count();
            $completedBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'completed')->count();
            $rejectedBookings  = VehicleBooking::where('company_id', $companyId)->where('status', 'rejected')->count();

            // ── Monthly chart — all 12 months, zero-filled ────────────────────
            $raw = VehicleBooking::where('company_id', $companyId)
                ->whereYear('created_at', date('Y'))
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(created_at)')
                ->orderByRaw('MONTH(created_at)')
                ->pluck('count', 'month');

            $months = collect(range(1, 12));
            $labels = $months->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
            $data   = $months->map(fn($m) => (int) ($raw[$m] ?? 0))->toArray();

            $kpis = [
                ['label' => 'Total Bookings', 'value' => $totalBookings,     'color' => 'blue',   'icon' => 'truck'],
                ['label' => 'Pending',         'value' => $pendingBookings,   'color' => 'yellow', 'icon' => 'clock'],
                ['label' => 'Approved',        'value' => $approvedBookings,  'color' => 'green',  'icon' => 'check-circle'],
                ['label' => 'Completed',       'value' => $completedBookings, 'color' => 'purple', 'icon' => 'check-badge'],
            ];

            $bookings = $this->showList
                ? VehicleBooking::where('company_id', $companyId)
                    ->with(['vehicle', 'user', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

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
                    ['label' => 'Completed',       'value' => 0, 'color' => 'purple', 'icon' => 'check-badge'],
                ],
                'labels'   => [],
                'data'     => [],
                'bookings' => collect(),
            ]);
        }
    }
}
