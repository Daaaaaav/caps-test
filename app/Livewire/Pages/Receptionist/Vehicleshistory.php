<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use Carbon\Carbon;

#[Layout('layouts.receptionist')]
#[Title('Vehicle History')]
class Vehicleshistory extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';
    protected string $tz = 'Asia/Jakarta';

    // Filters
    public string $q = '';
    public ?int $vehicleFilter = null;

    /**
     * done     => status completed
     * rejected => status rejected
     */
    public string $statusTab = 'done';

    // Include deleted checkbox
    public bool $includeDeleted = false;

    // Date filter (single date)
    public ?string $selectedDate = null;   // 'YYYY-MM-DD' atau null

    // Sort filter
    public string $sortFilter = 'recent';  // recent | oldest | nearest

    // Pagination
    public int $perPage = 5;

    protected $queryString = [
        'q'              => ['except' => ''],
        'vehicleFilter'  => ['except' => null],
        'statusTab'      => ['except' => 'done'],
        'includeDeleted' => ['except' => false],
        'selectedDate'   => ['except' => null],
        'sortFilter'     => ['except' => 'recent'],
        'page'           => ['except' => 1],
    ];

    public function updatingQ(): void                { $this->resetPage(); }
    public function updatingVehicleFilter(): void    { $this->resetPage(); }
    public function updatingStatusTab(): void        { $this->resetPage(); }
    public function updatingIncludeDeleted(): void   { $this->resetPage(); }
    public function updatingSelectedDate(): void     { $this->resetPage(); }
    public function updatingSortFilter(): void       { $this->resetPage(); }

    public function mount(): void
    {
        if (!in_array($this->statusTab, ['done', 'rejected'], true)) {
            $this->statusTab = 'done';
        }
        if (!in_array($this->sortFilter, ['recent', 'oldest', 'nearest'], true)) {
            $this->sortFilter = 'recent';
        }
    }

    /**
     * Soft delete untuk status 'completed' (Done) dan 'rejected'.
     */
    public function softDelete(int $vehiclebookingId): void
    {
        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?? 0);

        $booking = VehicleBooking::where('company_id', $companyId)
            ->where('vehiclebooking_id', $vehiclebookingId)
            ->first();

        if (!$booking) {
            session()->flash('error', 'Data tidak ditemukan.');
            return;
        }

        if (!in_array($booking->status, ['completed', 'rejected'], true)) {
            session()->flash('error', 'Hanya data Completed (Done) atau Rejected yang bisa dihapus.');
            return;
        }

        if (method_exists($booking, 'delete')) {
            $booking->delete();
            session()->flash('success', "Data #{$vehiclebookingId} berhasil dihapus (soft delete).");
        } else {
            session()->flash('error', 'Model belum mendukung soft delete.');
        }

        $this->resetPage();
    }

    /**
     * Restore soft-deleted row.
     */
    public function restore(int $vehiclebookingId): void
    {
        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?? 0);

        $booking = VehicleBooking::withTrashed()
            ->where('company_id', $companyId)
            ->where('vehiclebooking_id', $vehiclebookingId)
            ->first();

        if (!$booking) {
            session()->flash('error', 'Data tidak ditemukan untuk di-restore.');
            return;
        }

        if (method_exists($booking, 'restore') && $booking->trashed()) {
            $booking->restore();
            session()->flash('success', "Data #{$vehiclebookingId} berhasil direstore.");
        } else {
            session()->flash('error', 'Data tidak dalam kondisi terhapus atau model belum mendukung restore.');
        }

        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?? 0);

        $query = VehicleBooking::where('company_id', $companyId);

        // Include / exclude soft-deleted
        if ($this->includeDeleted) {
            $query->withTrashed();
        }

        // Status filter
        if ($this->statusTab === 'rejected') {
            $query->where('status', 'rejected');
        } else {
            $query->where('status', 'completed');
        }

        // Search
        if (strlen(trim($this->q)) > 0) {
            $q = trim($this->q);
            $query->where(function ($qq) use ($q) {
                $qq->where('purpose', 'like', "%{$q}%")
                   ->orWhere('destination', 'like', "%{$q}%")
                   ->orWhere('borrower_name', 'like', "%{$q}%");
            });
        }

        // Filter kendaraan
        if ($this->vehicleFilter) {
            $query->where('vehicle_id', $this->vehicleFilter);
        }

        // Filter tanggal (single date)
        if (!empty($this->selectedDate)) {
            $query->whereDate('start_at', $this->selectedDate);
        }

        // Sorting
        $now = Carbon::now($this->tz);
        switch ($this->sortFilter) {
            case 'oldest':
                $query->orderBy('start_at', 'asc');
                break;
            case 'nearest':
                $query->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, start_at, ?))', [$now]);
                break;
            case 'recent':
            default:
                $query->orderBy('start_at', 'desc');
                break;
        }

        $bookings = $query->paginate($this->perPage);

        // Data kendaraan untuk label
        $vehicles = Vehicle::where('company_id', $companyId)
            ->get(['vehicle_id', 'name', 'plate_number']);

        $vehicleMap = $vehicles->mapWithKeys(function ($v) {
            $label = $v->name ?? $v->plate_number ?? ('#' . $v->vehicle_id);
            return [$v->vehicle_id => $label];
        })->toArray();

        return view('livewire.pages.receptionist.vehicleshistory', [
            'bookings'   => $bookings,
            'vehicleMap' => $vehicleMap,
            'vehicles'   => $vehicles,
        ]);
    }
}
