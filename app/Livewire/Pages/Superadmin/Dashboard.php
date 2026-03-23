<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\BookingRoom;
use App\Models\VehicleBooking;
use App\Models\Delivery;

#[Layout('layouts.superadmin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public $activeFilter = 'all';

    public function setFilter($type)
    {
        $this->activeFilter = $type;
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // ===== KPI STATS =====
        $stats = [
            [
                'key' => 'all',
                'label' => 'All Activity',
                'value' => 
                    BookingRoom::where('company_id', $companyId)->count() +
                    VehicleBooking::where('company_id', $companyId)->count(),
                'trend' => 12,
                'direction' => 'up'
            ],
            [
                'key' => 'room',
                'label' => 'Room Bookings',
                'value' => BookingRoom::where('company_id', $companyId)->count(),
                'trend' => 5,
                'direction' => 'up'
            ],
            [
                'key' => 'vehicle',
                'label' => 'Vehicle Bookings',
                'value' => VehicleBooking::where('company_id', $companyId)->count(),
                'trend' => -3,
                'direction' => 'down'
            ],
            [
                'key' => 'users',
                'label' => 'Receptionists',
                'value' => User::where('company_id', $companyId)
                    ->whereHas('role', fn($q) => $q->where('name', 'Receptionist'))
                    ->count(),
                'trend' => 2,
                'direction' => 'up'
            ],
        ];

        // ===== CHART DATA (dummy monthly aggregation for now) =====
        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $room = array_fill(0, 12, 0);
        $vehicle = array_fill(0, 12, 0);

        // 👉 Replace with real monthly queries later if needed

        // ===== FILTER LOGIC =====
        if ($this->activeFilter === 'room') {
            $datasets = [
                [
                    'label' => 'Room Bookings',
                    'data' => $room,
                    'borderColor' => '#2563eb',
                ]
            ];
        } elseif ($this->activeFilter === 'vehicle') {
            $datasets = [
                [
                    'label' => 'Vehicle Bookings',
                    'data' => $vehicle,
                    'borderColor' => '#059669',
                ]
            ];
        } else {
            $datasets = [
                [
                    'label' => 'Room Bookings',
                    'data' => $room,
                    'borderColor' => '#2563eb',
                ],
                [
                    'label' => 'Vehicle Bookings',
                    'data' => $vehicle,
                    'borderColor' => '#059669',
                ]
            ];
        }

        return view('livewire.pages.superadmin.dashboard', [
            'stats' => $stats,
            'labels' => $labels,
            'datasets' => $datasets,
            'activeFilter' => $this->activeFilter,
        ]);
    }
}
