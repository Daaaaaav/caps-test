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
        $companyId = Auth::user()->company_id;

        // Get booking stats
        $totalBookings = BookingRoom::where('company_id', $companyId)->count();
        $pendingBookings = BookingRoom::where('company_id', $companyId)->where('status', 'pending')->count();
        $approvedBookings = BookingRoom::where('company_id', $companyId)->where('status', 'approved')->count();
        $rejectedBookings = BookingRoom::where('company_id', $companyId)->where('status', 'rejected')->count();

        // Chart data based on view type
        if ($this->viewType === 'monthly') {
            $stats = BookingRoom::where('company_id', $companyId)
                ->selectRaw('MONTH(created_at) as period, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $labels = $stats->pluck('period')->map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)))->toArray();
        } else {
            $stats = BookingRoom::where('company_id', $companyId)
                ->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $labels = $stats->pluck('period')->map(fn($d) => date('M d', strtotime($d)))->toArray();
        }

        $data = $stats->pluck('count')->toArray();

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
    }
}
