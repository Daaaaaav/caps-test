<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use App\Models\VehicleBookingPhoto;

#[Layout('layouts.receptionist')]
#[Title('Vehicle Status')]
class Vehiclestatus extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';
    protected string $tz = 'Asia/Jakarta';

    // Filters/state
    public string $q = '';
    public ?int $vehicleFilter = null;
    public ?string $selectedDate = null;   // YYYY-MM-DD
    public string $statusTab = 'pending';  // pending | approved | on_progress | returned
    public string $sortFilter = 'recent';  // recent | oldest | nearest
    public int $perPage = 10;
    public bool $includeDeleted = false;

    /** cache */
    public $vehicles;
    /** @var array<int,string> */
    public array $vehicleMap = [];
    /** @var array<int,array{before:int,after:int}> */
    public array $photoCounts = [];

    // Reject modal state
    public bool $showRejectModal = false;
    public ?int $rejectId = null;
    public string $rejectNote = '';

    // *** BARU: Detail modal state ***
    public bool $showDetailModal = false;
    public ?VehicleBooking $selectedBooking = null;
    /** @var array{before: array, after: array} */
    public array $selectedPhotos = ['before' => [], 'after' => []];
    // *** END BARU ***

    protected $queryString = [
        'q' => ['except' => ''],
        'vehicleFilter' => ['except' => null],
        'selectedDate' => ['except' => null],
        'statusTab' => ['except' => 'pending'],
        'sortFilter' => ['except' => 'recent'],
        'page' => ['except' => 1],
    ];

    // Reset page on filter change
    public function updatedQ()
    {
        $this->resetPage();
    }
    public function updatedVehicleFilter()
    {
        $this->resetPage();
    }
    public function updatedSelectedDate()
    {
        $this->resetPage();
    }
    public function updatedStatusTab()
    {
        $this->resetPage();
    }
    public function updatedSortFilter()
    {
        $this->resetPage();
    }
    public function updatedIncludeDeleted()
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->vehicles = Vehicle::orderBy('name')->get();
        $this->vehicleMap = $this->vehicles
            ->mapWithKeys(fn($v) => [(int) $v->vehicle_id => (string) ($v->name ?? $v->plate_number ?? ('#' . $v->vehicle_id))])
            ->toArray();
    }

    public function render()
    {
        $bookings = VehicleBooking::query()
            ->when(!$this->includeDeleted, fn(Builder $q) => $q->whereNull('deleted_at'))
            ->when($this->includeDeleted, fn(Builder $q) => $q->withTrashed())
            ->when($this->vehicleFilter, fn(Builder $q) => $q->where('vehicle_id', $this->vehicleFilter))
            ->when($this->q !== '', function (Builder $q) {
                $like = '%' . $this->q . '%';
                $q->where(function (Builder $qq) use ($like) {
                    $qq->where('purpose', 'like', $like)
                        ->orWhere('destination', 'like', $like)
                        ->orWhere('borrower_name', 'like', $like);
                });
            })
            ->when($this->selectedDate, fn(Builder $q) => $q->whereDate('start_at', $this->selectedDate))
            ->when($this->statusTab, fn(Builder $q) => $q->where('status', $this->statusTab))
            ->when($this->sortFilter === 'recent', fn(Builder $q) => $q->orderByDesc('vehiclebooking_id'))
            ->when($this->sortFilter === 'oldest', fn(Builder $q) => $q->orderBy('vehiclebooking_id'))
            ->when($this->sortFilter === 'nearest', fn(Builder $q) => $q->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, NOW(), start_at))'))
            ->paginate($this->perPage);

        $ids = $bookings->pluck('vehiclebooking_id')->all();
        $this->photoCounts = $this->buildPhotoCounts($ids);

        return view('livewire.pages.receptionist.vehiclestatus', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * @param  array<int> $bookingIds
     * @return array<int,array{before:int,after:int}>
     */
    protected function buildPhotoCounts(array $bookingIds): array
    {
        if (empty($bookingIds))
            return [];
        $rows = VehicleBookingPhoto::selectRaw('vehiclebooking_id, photo_type, COUNT(*) as c')
            ->whereIn('vehiclebooking_id', $bookingIds)
            ->groupBy('vehiclebooking_id', 'photo_type')
            ->get();

        $out = [];
        foreach ($bookingIds as $id)
            $out[$id] = ['before' => 0, 'after' => 0];
        foreach ($rows as $r) {
            $vb = (int) $r->vehiclebooking_id;
            $type = $r->photo_type === 'after' ? 'after' : 'before';
            $out[$vb][$type] = (int) $r->c;
        }
        return $out;
    }

    /* =========================
     * Actions
     * ========================= */

    public function approve(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                /** @var VehicleBooking $b */
                $b = VehicleBooking::lockForUpdate()
                    ->when($this->includeDeleted, fn($q) => $q->withTrashed())
                    ->findOrFail($id);

                if ($b->status !== 'pending') {
                    throw new \RuntimeException("Booking #{$b->vehiclebooking_id} bukan status pending.");
                }
                $b->status = 'approved';
                $b->save();
            });

            $this->dispatch('toast', type: 'success', title: 'Approved', message: 'Booking disetujui.');
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'warning', title: 'Tidak Bisa Disetujui', message: $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal menyetujui: ' . $e->getMessage());
        }
    } 

    /** Open modal to ask for reject reason */
    public function confirmReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectNote = '';
        $this->showRejectModal = true;
    }

    /** Close/cancel modal */
    public function cancelReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectId = null;
        $this->rejectNote = '';
    }

    /** Validate + perform rejection with required note */
    public function submitReject(): void
    {
        $data = $this->validate([
            'rejectNote' => 'required|string|min:5|max:2000',
            'rejectId' => 'required|integer',
        ]);

        try {
            DB::transaction(function () use ($data) {
                /** @var VehicleBooking $b */
                $b = VehicleBooking::lockForUpdate()
                    ->when($this->includeDeleted, fn($q) => $q->withTrashed())
                    ->findOrFail($data['rejectId']);

                if ($b->status !== 'pending') {
                    throw new \RuntimeException("Booking #{$b->vehiclebooking_id} bukan status pending.");
                }

                // Store reason in `notes` (adjust if you have a dedicated reject column)
                $prefix = '[Rejected] ';
                $reason = trim($data['rejectNote']);
                // Check if notes already exists to append it nicely
                $b->notes = trim(($b->notes ? $b->notes . "\n" : '') . $prefix . $reason);
                $b->status = 'rejected';
                $b->save();
            });

            $this->showRejectModal = false;
            $this->rejectId = null;
            $this->rejectNote = '';

            $this->dispatch('toast', type: 'info', title: 'Rejected', message: 'Booking ditolak dengan alasan.');
            $this->resetPage();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation errors to be caught by Livewire/Blade error messages
            throw $e;
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'warning', title: 'Tidak Bisa Ditolak', message: $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal menolak: ' . $e->getMessage());
        }
    }

    public function markReturned(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $b = VehicleBooking::lockForUpdate()
                    ->when($this->includeDeleted, fn($q) => $q->withTrashed())
                    ->findOrFail($id);
                if (!in_array($b->status, ['approved', 'on_progress'], true)) {
                    throw new \RuntimeException("Booking #{$b->vehiclebooking_id} belum on progress.");
                }
                $b->status = 'returned';
                $b->save();
            });

            $this->dispatch('toast', type: 'success', title: 'Returned', message: 'Status diubah ke Returned.');
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'warning', title: 'Tidak Bisa', message: $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal update: ' . $e->getMessage());
        }
    }

    public function markDone(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $b = VehicleBooking::lockForUpdate()
                    ->when($this->includeDeleted, fn($q) => $q->withTrashed())
                    ->findOrFail($id);
                if ($b->status !== 'returned') {
                    throw new \RuntimeException("Booking #{$b->vehiclebooking_id} belum Returned.");
                }
                $afterCount = VehicleBookingPhoto::where('vehiclebooking_id', $b->vehiclebooking_id)
                    ->where('photo_type', 'after')
                    ->count();
                if ($afterCount < 1) {
                    throw new \RuntimeException('Upload minimal 1 foto AFTER terlebih dahulu.');
                }
                $b->status = 'completed';
                $b->save();
            });

            $this->dispatch('toast', type: 'success', title: 'Completed', message: 'Booking ditandai selesai.');
            $this->resetPage();
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'warning', title: 'Tidak Bisa', message: $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal update: ' . $e->getMessage());
        }
    }

    // *** BARU: Metode untuk Detail Modal ***
    public function showDetails(int $id): void
    {
        try {
            $booking = VehicleBooking::when($this->includeDeleted, fn($q) => $q->withTrashed())
                ->findOrFail($id);

            $photos = VehicleBookingPhoto::where('vehiclebooking_id', $id)
                ->with('user') // Pastikan relasi user ada di model VehicleBookingPhoto
                ->orderBy('created_at')
                ->get();

            $this->selectedBooking = $booking;

            // Sort photos
            $before = [];
            $after = [];
            foreach ($photos as $photo) {
                if ($photo->photo_type === 'after') {
                    $after[] = $photo;
                } else {
                    $before[] = $photo;
                }
            }
            $this->selectedPhotos = ['before' => $before, 'after' => $after];

            $this->showDetailModal = true;
            $this->resetErrorBag();

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal memuat detail: ' . $e->getMessage());
        }
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedBooking = null;
        $this->selectedPhotos = ['before' => [], 'after' => []];
        $this->resetErrorBag();
    }
    // *** END BARU ***
}