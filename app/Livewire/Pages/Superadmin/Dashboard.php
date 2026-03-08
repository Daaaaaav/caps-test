<?php

namespace App\Livewire\Pages\Superadmin;

use App\Models\Announcement;
use App\Models\BookingRoom;
use App\Models\VehicleBooking;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.superadmin')]
#[Title('Superadmin Dashboard')]
class Dashboard extends Component
{
    public string $admin_name = '';

    public function mount(): void
    {
        $this->admin_name = Auth::user()->full_name ?? 'Superadmin User';
    }

    public function tick(): void
    {
        //
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // ===== BASIC STATS (Filtered by Company) =====
        $stats = [
            ['label' => 'Total Users', 'value' => User::where('company_id', $companyId)->count()],
            ['label' => 'Total Departments', 'value' => Department::where('company_id', $companyId)->count()],
            ['label' => 'Total Announcements', 'value' => Announcement::where('company_id', $companyId)->count()],
        ];

        // ===== MONTHLY COUNTS (Filtered by Company) =====
        $roomBookings = BookingRoom::where('company_id', $companyId)
            ->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->whereYear('created_at', now()->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $vehicleBookings = VehicleBooking::where('company_id', $companyId)
            ->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->whereYear('created_at', now()->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $tickets = Ticket::where('company_id', $companyId)
            ->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->whereYear('created_at', now()->year)
            ->groupBy('m')->pluck('c', 'm')->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartData = [
            'labels' => $months,
            'room' => array_map(fn($i) => (int) ($roomBookings[$i] ?? 0), range(1, 12)),
            'vehicle' => array_map(fn($i) => (int) ($vehicleBookings[$i] ?? 0), range(1, 12)),
            'ticket' => array_map(fn($i) => (int) ($tickets[$i] ?? 0), range(1, 12)),
        ];

        // ===== TICKETING CHARTS (Filtered by Company) =====
        $priorityCountsRaw = Ticket::where('company_id', $companyId)
            ->selectRaw('priority, COUNT(*) as c')
            ->groupBy('priority')->pluck('c', 'priority')->toArray();

        $priorityCounts = [
            'low' => (int) ($priorityCountsRaw['low'] ?? 0),
            'medium' => (int) ($priorityCountsRaw['medium'] ?? 0),
            'high' => (int) ($priorityCountsRaw['high'] ?? 0),
        ];

        $statusCountsRaw = Ticket::where('company_id', $companyId)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')->pluck('c', 'status')->toArray();

        $statusCounts = [
            'OPEN' => (int) ($statusCountsRaw['OPEN'] ?? 0),
            'IN_PROGRESS' => (int) ($statusCountsRaw['IN_PROGRESS'] ?? 0),
            'RESOLVED' => (int) ($statusCountsRaw['RESOLVED'] ?? 0),
            'CLOSED' => (int) ($statusCountsRaw['CLOSED'] ?? 0),
        ];

        $avgPriorityRaw = Ticket::where('company_id', $companyId)
            ->whereYear('created_at', now()->year)
            ->selectRaw("
                MONTH(created_at) as m,
                AVG(
                    CASE priority
                        WHEN 'low' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'high' THEN 3
                    END
                ) as avgp
            ")
            ->groupBy('m')->pluck('avgp', 'm')->toArray();

        $ticketCharts = [
            'labels' => $months,
            'priorityCounts' => $priorityCounts,
            'statusCounts' => $statusCounts,
            'avgPriority' => array_map(fn($i) => round((float) ($avgPriorityRaw[$i] ?? 0), 2), range(1, 12)),
        ];

        return view('livewire.pages.superadmin.dashboard', [
            'admin_name' => $this->admin_name,
            'stats' => $stats,
            'chartData' => $chartData,
            'ticketCharts' => $ticketCharts,
        ]);
    }
}
