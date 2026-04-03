<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BookingRoom;

#[Layout('layouts.superadmin')]
#[Title('Room Booking Statistics')]
class RoomBookingStatistics extends Component
{
    public $viewType = 'monthly';
    public $showList = false;

    public function setViewType($type)
    {
        $this->viewType = $type;
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
            $totalBookings = BookingRoom::where('company_id', $companyId)->count();
            $pendingBookings = BookingRoom::where('company_id', $companyId)->where('status', 'pending')->count();
            $approvedBookings = BookingRoom::where('company_id', $companyId)->where('status', 'approved')->count();
            $rejectedBookings = BookingRoom::where('company_id', $companyId)->where('status', 'rejected')->count();

            // Use dummy data if no real data
            if ($totalBookings == 0) {
                $totalBookings = 45;
                $pendingBookings = 8;
                $approvedBookings = 32;
                $rejectedBookings = 5;
            }

            // Chart data based on view type
            if ($this->viewType === 'monthly') {
                $stats = BookingRoom::where('company_id', $companyId)
                    ->selectRaw('MONTH(created_at) as period, COUNT(*) as count')
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

                if ($stats->isEmpty()) {
                    // Dummy monthly data
                    $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $data = [3, 5, 4, 6, 3, 7, 5, 6, 8, 5, 4, 6];
                } else {
                    $labels = $stats->pluck('period')->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
                    $data = $stats->pluck('count')->toArray();
                }
            } else {
                $stats = BookingRoom::where('company_id', $companyId)
                    ->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

                if ($stats->isEmpty()) {
                    // TO DO: Change dummy retrieval data after done with AI
                    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $data = [2, 3, 1, 4, 2, 3, 2];
                } else {
                    $labels = $stats->pluck('period')->map(fn($d) => date('M d', strtotime($d)))->toArray();
                    $data = $stats->pluck('count')->toArray();
                }
            }

            $kpis = [
                ['label' => 'Total Bookings', 'value' => $totalBookings, 'color' => 'blue'],
                ['label' => 'Pending', 'value' => $pendingBookings, 'color' => 'yellow'],
                ['label' => 'Approved', 'value' => $approvedBookings, 'color' => 'green'],
                ['label' => 'Rejected', 'value' => $rejectedBookings, 'color' => 'red'],
            ];

            // Get booking items if list is shown
            $bookings = $this->showList 
                ? BookingRoom::where('company_id', $companyId)
                    ->with(['room', 'user', 'department'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

            return view('livewire.pages.superadmin.room-booking-statistics', [
                'kpis' => $kpis,
                'labels' => $labels,
                'data' => $data,
                'bookings' => $bookings,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', 
                type: 'error',
                title: 'Error',
                message: 'Failed to retrieve room booking data: ' . $e->getMessage(),
                duration: 4000
            );

            return view('livewire.pages.superadmin.room-booking-statistics', [
                'kpis' => [
                    ['label' => 'Total Bookings', 'value' => 0, 'color' => 'blue'],
                    ['label' => 'Pending', 'value' => 0, 'color' => 'yellow'],
                    ['label' => 'Approved', 'value' => 0, 'color' => 'green'],
                    ['label' => 'Rejected', 'value' => 0, 'color' => 'red'],
                ],
                'labels' => [],
                'data' => [],
                'bookings' => collect([]),
            ]);
        }
    }
}
