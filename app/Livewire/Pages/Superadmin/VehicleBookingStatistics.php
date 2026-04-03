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
        try {
            $companyId = Auth::user()->company_id;

            // Get booking stats
            $totalBookings = VehicleBooking::where('company_id', $companyId)->count();
            $pendingBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'pending')->count();
            $approvedBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'approved')->count();
            $completedBookings = VehicleBooking::where('company_id', $companyId)->where('status', 'completed')->count();

            // Use dummy data if no real data
            if ($totalBookings == 0) {
                $totalBookings = 38;
                $pendingBookings = 6;
                $approvedBookings = 25;
                $completedBookings = 7;
            }

            // Monthly chart data
            $monthlyStats = VehicleBooking::where('company_id', $companyId)
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            if ($monthlyStats->isEmpty()) {
                // TO DO: Change dummy retrieval data after done with AI
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $data = [2, 4, 3, 5, 2, 6, 4, 5, 7, 4, 3, 5];
            } else {
                $labels = $monthlyStats->pluck('month')->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
                $data = $monthlyStats->pluck('count')->toArray();
            }

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
        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error',
                title: 'Error',
                message: 'Failed to retrieve vehicle booking data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.vehicle-booking-statistics', [
                'kpis' => [
                    ['label' => 'Total Bookings', 'value' => 0, 'color' => 'blue', 'icon' => 'truck'],
                    ['label' => 'Pending', 'value' => 0, 'color' => 'yellow', 'icon' => 'clock'],
                    ['label' => 'Approved', 'value' => 0, 'color' => 'green', 'icon' => 'check-circle'],
                    ['label' => 'Completed', 'value' => 0, 'color' => 'purple', 'icon' => 'check-badge'],
                ],
                'labels' => [],
                'data' => [],
                'bookings' => collect([]),
            ]);
        }
    }
}
