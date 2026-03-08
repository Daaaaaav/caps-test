<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use App\Models\VehicleBookingPhoto;
use Carbon\Carbon;

#[Layout('layouts.superadmin')]
#[Title('Vehicle History')]
class Bookingvehicle extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';
    protected string $tz = 'Asia/Jakarta';

    public string $q = '';
    public ?int $vehicleFilter = null;
    public string $statusTab = 'done';
    public bool $includeDeleted = false;
    public ?string $selectedDate = null;
    public string $sortFilter = 'recent';
    public int $perPage = 5;

    public array $photosByBooking = [];

    protected $queryString = [
        'q'              => ['except' => ''],
        'vehicleFilter'  => ['except' => null],
        'statusTab'      => ['except' => 'done'],
        'includeDeleted' => ['except' => false],
        'selectedDate'   => ['except' => null],
        'sortFilter'     => ['except' => 'recent'],
        'page'           => ['except' => 1],
    ];

    public function updatingQ()              { $this->resetPage(); }
    public function updatingVehicleFilter()  { $this->resetPage(); }
    public function updatingStatusTab()      { $this->resetPage(); }
    public function updatingIncludeDeleted() { $this->resetPage(); }
    public function updatingSelectedDate()   { $this->resetPage(); }
    public function updatingSortFilter()     { $this->resetPage(); }

    public function mount(): void
    {
        if (!in_array($this->statusTab, ['done','rejected'], true))  $this->statusTab = 'done';
        if (!in_array($this->sortFilter, ['recent','oldest','nearest'], true)) $this->sortFilter = 'recent';
    }

    public function deletePhoto(int $photoId): void
    {
        VehicleBookingPhoto::findOrFail($photoId)->delete();
        $this->dispatch('toast', type:'success', title:'Deleted', message:'Photo soft-deleted.', duration:2500);
        $this->refreshPhotosForCurrentPage();
    }
    public function restorePhoto(int $photoId): void
    {
        VehicleBookingPhoto::withTrashed()->findOrFail($photoId)->restore();
        $this->dispatch('toast', type:'success', title:'Restored', message:'Photo restored.', duration:2500);
        $this->refreshPhotosForCurrentPage();
    }
    public function forceDeletePhoto(int $photoId): void
    {
        $row = VehicleBookingPhoto::withTrashed()->findOrFail($photoId);
        if ($row->photo_path && !preg_match('#^https?://#', $row->photo_path)) {
            Storage::disk('public')->delete($row->photo_path);
        }
        $row->forceDelete();
        $this->dispatch('toast', type:'success', title:'Removed', message:'Photo permanently deleted.', duration:2500);
        $this->refreshPhotosForCurrentPage();
    }
    private function refreshPhotosForCurrentPage(): void { $this->q = $this->q; }

    public function editBooking(int $bookingId): void
    {
        $this->dispatch('toast', type:'info', title:'Edit Action', message:"Ready to edit Booking #{$bookingId}.", duration:3000);
    }
    
    public function deleteBooking(int $bookingId): void
    {
        VehicleBooking::findOrFail($bookingId)->delete();
        $this->dispatch('toast', type:'success', title:'Deleted', message:"Booking #{$bookingId} soft-deleted.", duration:2500);
        $this->resetPage();
    }

    public function restoreBooking(int $bookingId): void
    {
        VehicleBooking::withTrashed()->findOrFail($bookingId)->restore();
        $this->dispatch('toast', type:'success', title:'Restored', message:"Booking #{$bookingId} restored.", duration:2500);
        $this->resetPage();
    }

    public function forceDeleteBooking(int $bookingId): void
    {
        $booking = VehicleBooking::withTrashed()->findOrFail($bookingId);
        
        $photos = VehicleBookingPhoto::withTrashed()->where('vehiclebooking_id', $bookingId)->get();
        foreach ($photos as $photo) {
            if ($photo->photo_path && !preg_match('#^https?://#', $photo->photo_path)) {
                Storage::disk('public')->delete($photo->photo_path);
            }
            $photo->forceDelete();
        }
        
        $booking->forceDelete();
        
        $this->dispatch('toast', type:'success', title:'Removed', message:"Booking #{$bookingId} permanently deleted.", duration:2500);
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?? 0);

        $query = VehicleBooking::where('company_id', $companyId);

        if ($this->includeDeleted) $query->withTrashed();

        $query->where('status', $this->statusTab === 'rejected' ? 'rejected' : 'completed');

        if (trim($this->q) !== '') {
            $q = trim($this->q);
            $query->where(function ($qq) use ($q) {
                $qq->where('purpose', 'like', "%{$q}%")
                   ->orWhere('destination', 'like', "%{$q}%")
                   ->orWhere('borrower_name', 'like', "%{$q}%");
            });
        }

        if ($this->vehicleFilter) $query->where('vehicle_id', $this->vehicleFilter);
        if (!empty($this->selectedDate)) $query->whereDate('start_at', $this->selectedDate);

        $now = Carbon::now($this->tz);
        match ($this->sortFilter) {
            'oldest'  => $query->orderBy('start_at', 'asc'),
            'nearest' => $query->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, start_at, ?))', [$now]),
            default   => $query->orderBy('start_at', 'desc'),
        };

        $bookings = $query->paginate($this->perPage);

        $vehicles = Vehicle::where('company_id', $companyId)->get(['vehicle_id','name','plate_number']);
        $vehicleMap = $vehicles->mapWithKeys(fn($v) => [
            $v->vehicle_id => ($v->name ?? $v->plate_number ?? ('#'.$v->vehicle_id))
        ])->toArray();

        $ids = method_exists($bookings, 'pluck')
            ? $bookings->pluck('vehiclebooking_id')->filter()->values()->all()
            : [];

        $this->photosByBooking = [];
        $photoCounts = [];
        foreach ($ids as $bid) {
            $this->photosByBooking[$bid] = ['before' => collect(), 'after' => collect()];
            $photoCounts[$bid] = ['before' => 0, 'after' => 0];
        }

        if (!empty($ids)) {
            $photoQuery = VehicleBookingPhoto::select(['id','vehiclebooking_id','photo_type','photo_path','deleted_at'])
                ->whereIn('vehiclebooking_id', $ids);

            if ($this->includeDeleted) $photoQuery->withTrashed();

            $allPhotos = $photoQuery->orderBy('photo_type')->latest('id')->get();

            foreach ($allPhotos as $p) {
                $type = $p->photo_type === 'after' ? 'after' : 'before';

                if (!array_key_exists($p->vehiclebooking_id, $this->photosByBooking)) {
                    $this->photosByBooking[$p->vehiclebooking_id] = ['before' => collect(), 'after' => collect()];
                    $photoCounts[$p->vehiclebooking_id] = ['before' => 0, 'after' => 0];
                }

                $this->photosByBooking[$p->vehiclebooking_id][$type]->push($p);

                if ($this->includeDeleted || is_null($p->deleted_at)) {
                    $photoCounts[$p->vehiclebooking_id][$type]++;
                }
            }
        }

        return view('livewire.pages.superadmin.bookingvehicle', [
            'bookings'       => $bookings,
            'vehicleMap'     => $vehicleMap,
            'vehicles'       => $vehicles,
            'photoCounts'    => $photoCounts,
            'statusTab'      => $this->statusTab,
            'includeDeleted' => $this->includeDeleted,
            'selectedDate'   => $this->selectedDate,
            'sortFilter'     => $this->sortFilter,
            'vehicleFilter'  => $this->vehicleFilter,
        ]);
    }
}