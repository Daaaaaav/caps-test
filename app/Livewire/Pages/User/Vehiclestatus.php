<?php

namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use App\Models\VehicleBookingPhoto;

#[Layout('layouts.app')]
#[Title('Vehicle Booking')]
class Vehiclestatus extends Component
{
    use WithPagination;

    public string $q = '';
    public ?int $vehicleFilter = null;
    public string $dbStatusFilter = 'all';
    public string $sortFilter = 'recent';
    public int $perPage = 10;
    protected string $tz = 'Asia/Jakarta';

    protected $queryString = [
        'q'              => ['except' => ''],
        'vehicleFilter'  => ['except' => null],
        'dbStatusFilter' => ['except' => 'all'],
        'sortFilter'     => ['except' => 'recent'],
        'page'           => ['except' => 1],
    ];

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingVehicleFilter()
    {
        $this->resetPage();
    }
    public function updatingDbStatusFilter()
    {
        $this->resetPage();
    }
    public function updatingSortFilter()
    {
        $this->resetPage();
    }

    /**
     * NEW: buka halaman Bookvehicle dengan query ?edit=<id>
     * Ganti 'bookvehicle' jika nama route-mu beda.
     */
    public function openUpload(int $bookingId)
    {
        return redirect()->route('bookvehicle', ['edit' => $bookingId]);
    }

    public function render()
    {
        $user = Auth::user();
        $userId = $user?->user_id ?? Auth::id();
        $companyId = (int) ($user?->company_id ?? 0);
        $now = Carbon::now($this->tz);

        $query = VehicleBooking::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId);

        if (strlen(trim($this->q)) > 0) {
            $q = trim($this->q);

            // Cari kendaraan hanya pada kolom yang ada: name / plate_number
            $vehicleIds = Vehicle::where('company_id', $companyId)
                ->where(function ($v) use ($q) {
                    $v->where('name', 'like', "%{$q}%")
                        ->orWhere('plate_number', 'like', "%{$q}%");
                })
                ->pluck('vehicle_id')
                ->all();

            $query->where(function ($qq) use ($q, $vehicleIds) {
                $qq->where('purpose', 'like', "%{$q}%")
                    ->orWhere('destination', 'like', "%{$q}%")
                    ->orWhere('borrower_name', 'like', "%{$q}%")
                    ->orWhere('purpose_type', 'like', "%{$q}%");
                if (!empty($vehicleIds)) {
                    $qq->orWhereIn('vehicle_id', $vehicleIds);
                }
            });
        }

        if ($this->vehicleFilter) {
            $query->where('vehicle_id', $this->vehicleFilter);
        }

        if ($this->dbStatusFilter !== 'all') {
            $query->where('status', $this->dbStatusFilter);
        }

        switch ($this->sortFilter) {
            case 'oldest':
                $query->orderBy('start_at', 'asc');
                break;
            case 'nearest':
                $query->orderByRaw('CASE WHEN start_at >= ? THEN 0 ELSE 1 END', [$now->toDateTimeString()])
                    ->orderBy('start_at', 'asc');
                break;
            default:
                $query->orderBy('start_at', 'desc');
                break;
        }

        $bookings = $query->paginate($this->perPage);

        // Ambil kolom yang pasti ada saja
        $vehicles = Vehicle::where('company_id', $companyId)
            ->get(['vehicle_id', 'name', 'plate_number']);

        $vehicleMap = $vehicles->mapWithKeys(function ($v) {
            $label = $v->name ?? $v->plate_number ?? ('#' . $v->vehicle_id);
            return [$v->vehicle_id => $label];
        })->toArray();

        // Hitung jumlah foto before/after
        $ids = $bookings->getCollection()->pluck('vehiclebooking_id')->all();
        $photoCounts = [];
        if (!empty($ids)) {
            $rows = VehicleBookingPhoto::selectRaw('vehiclebooking_id, photo_type, COUNT(*) as c')
                ->whereIn('vehiclebooking_id', $ids)
                ->groupBy('vehiclebooking_id', 'photo_type')
                ->get();
            foreach ($rows as $r) {
                // FIX: Sanitize photo_type to prevent issues with whitespace or case sensitivity.
                $type = strtolower(trim($r->photo_type));
                if ($type === 'before' || $type === 'after') {
                    $photoCounts[$r->vehiclebooking_id][$type] = (int)$r->c;
                }
            }
        }

        return view('livewire.pages.user.vehiclestatus', [
            'bookings'    => $bookings,
            'vehicleMap'  => $vehicleMap,
            'vehicles'    => $vehicles,
            'photoCounts' => $photoCounts,
        ]);
    }
}
