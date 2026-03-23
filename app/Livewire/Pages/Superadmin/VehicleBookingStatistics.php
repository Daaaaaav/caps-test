<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleBooking;

#[Layout('layouts.superadmin')]
#[Title('Vehicle Booking Statistics')]
class VehicleBookingStatistics extends Component
{
    public $chartType = 'line';
    public $showList = false;

    public function setChartType($type)
    {
        $this->chartType = $type;
    }

    public function toggleList()
    {
        $this->showList = !$this->showList;
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // Get booking stats
        $totalBookings = VehicleBooking::where('company_id', $companyId)->count();
        $pendingBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'pending')->count();
        $approvedBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'approved')->count();
        $completedBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'completed')->count();

        // Monthly chart data
        $monthlyStats = VehicleBooking::where('company_id', $companyId)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $monthlyStats->pluck('month')->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
        $data = $monthlyStats->pluck('count')->toArray();

        $kpis = [
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'color' => 'blue', 'icon' => 'truck'],
            ['label' => 'Pending', 'value' => $pendingBookings, 'color' => 'yellow', 'icon' => 'clock'],
            ['label' => 'Approved', 'value' => $approvedBookings, 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Completed', 'value' => $completedBookings, 'color' => 'purple', 'icon' => 'check-badge'],
        ];

        // Get booking items if list is shown
        $bookings = $this->showList 
            ? VehicleBooking::where('company_id', $companyId)
                ->with(['vehicle', 'user', 'department'])
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        return view('livewire.pages.superadmin.vehicle-booking-statistics', [
            'kpis' => $kpis,
            'labels' => $labels,
            'data' => $data,
            'bookings' => $bookings,
        ]);
    }
}
