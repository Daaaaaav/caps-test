<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $visitors = 0;
    public $roomBookings = 0;
    public $vehicleBookings = 0;
    public $packages = 0;

    public function loadStats()
    {
        $this->visitors = DB::table('guestbook')->count();
        $this->roomBookings = DB::table('room_bookings')->count();
        $this->vehicleBookings = DB::table('vehicle_bookings')->count();
        $this->packages = DB::table('packages')->count();
    }

    public function mount()
    {
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.pages.superadmin.dashboard');
    }
}