<?php

namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Vehicle;
use App\Models\Department;
use App\Models\VehicleBooking;
use App\Models\VehicleBookingPhoto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
#[Title('Book Vehicle')]
class Bookvehicle extends Component
{
    use WithFileUploads;

    // Form Properties
    public $name;
    public $department_id;
    public $start_time;
    public $end_time;
    public $date_from;
    public $date_to;
    public $purpose;
    public $destination;
    public $odd_even_area = 'tidak';
    public $purpose_type = 'dinas';
    public $vehicle_id = null;
    public $has_sim_a = false;
    public $agree_terms = false;

    // Booking Mode: 'perday', '24hours', 'custom'
    public $booking_mode = 'perday';

    // --- FILE UPLOAD QUEUE SYSTEM ---
    public $collected_photos = []; // Wadah penampung semua foto
    public $temp_photos = [];      // Pintu masuk sementara (input file)

    // UI Data
    public $departments;
    public $vehicles;
    public $hasVehicles = false;
    public $availability = [];
    public $unavailableVehicleIds = [];
    public $recentBookings = [];

    public $booking = null;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->full_name ?? $user->name ?? null;
        $this->department_id = $user->department_id ?? null;
        $this_company_id = $user->company_id ?? 1;

        $this->departments = Department::where('company_id', $this_company_id)->orderBy('department_name')->get();
        $this->vehicles = Vehicle::where('company_id', $this_company_id)->where('is_active', true)->get();
        $this->hasVehicles = $this->vehicles->count() > 0;

        // Default: Today
        $this->date_from = Carbon::today()->format('Y-m-d');

        // Calculate initial dates/times
        $this->calculateDates();

        $this->loadAvailability();
        $this->loadRecentBookings();

        $bookingId = request()->query('id');
        if ($bookingId) {
            $this->loadBooking($bookingId, $user);
        }
    }

    // --- LOGIC "ANTRIAN" FOTO ---

    /**
     * Dijalankan otomatis saat user memilih file di input
     */
    public function updatedTempPhotos()
    {
        $this->validate([
            'temp_photos.*' => 'image|max:5120', // Max 5MB per file
        ]);

        // Pindahkan dari temp ke collected (Append)
        foreach ($this->temp_photos as $photo) {
            $this->collected_photos[] = $photo;
        }

        // Reset input agar bisa pilih file lagi
        $this->reset('temp_photos');
    }

    /**
     * Menghapus satu foto dari antrian
     */
    public function removePhoto($index)
    {
        array_splice($this->collected_photos, $index, 1);
    }

    // ------------------------------

    public function setBookingMode($mode)
    {
        $this->booking_mode = $mode;
        $this->calculateDates();
        $this->loadAvailability();
    }

    public function updatedDateFrom()
    {
        $this->calculateDates();
        $this->loadAvailability();
    }

    private function calculateDates()
    {
        if (!$this->date_from)
            return;

        if ($this->booking_mode === 'perday') {
            $this->start_time = '08:00';
            $this->end_time = '17:00';
            $this->date_to = $this->date_from;
        } elseif ($this->booking_mode === '24hours') {
            $this->start_time = '08:00';
            $this->end_time = '08:00';
            try {
                $this->date_to = Carbon::parse($this->date_from)->addDay()->format('Y-m-d');
            } catch (\Exception $e) {
                $this->date_to = $this->date_from;
            }
        }
    }

    public function loadBooking($id, $user)
    {
        $booking = VehicleBooking::where('vehiclebooking_id', $id)
            ->where('company_id', $user->company_id ?? 1)
            ->first();

        if (!$booking || ($booking->user_id != $user->user_id && $booking->department_id != $user->department_id)) {
            session()->flash('error', 'Booking not found or unauthorized.');
            return;
        }

        if (!in_array($booking->status, ['approved', 'returned'])) {
            session()->flash('error', 'Invalid status.');
            return;
        }

        $this->booking = $booking;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'department_id' => 'required|integer',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'start_time' => 'required',
            'end_time' => 'required',
            'purpose' => 'required|string|max:500',
            'odd_even_area' => 'required',
            'purpose_type' => 'required',
            'has_sim_a' => 'accepted',
            'agree_terms' => 'accepted',
            'vehicle_id' => 'nullable|integer|exists:vehicles,vehicle_id',
        ];
    }

    public function submitBooking()
    {
        $this->validate($this->rules());

        $user = Auth::user();
        $startAt = $this->combineDateTime($this->date_from, $this->start_time);
        $endAt = $this->combineDateTime($this->date_to, $this->end_time);

        if (Carbon::parse($endAt)->lte(Carbon::parse($startAt))) {
            $this->addError('end_time', 'End time must be after start time.');
            return;
        }

        if ($this->vehicle_id) {
            $isAvailable = $this->checkAvailabilityForVehicle($this->vehicle_id, $startAt, $endAt);
            if (!$isAvailable) {
                session()->flash('error', 'Vehicle is unavailable for selected time.');
                return;
            }
        }

        VehicleBooking::create([
            'company_id' => $user->company_id ?? 1,
            'user_id' => $user->user_id ?? Auth::id(),
            'vehicle_id' => $this->vehicle_id ?: null,
            'borrower_name' => $this->name,
            'department_id' => $this->department_id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'purpose' => $this->purpose,
            'destination' => $this->destination,
            'odd_even_area' => $this->odd_even_area,
            'purpose_type' => $this->purpose_type,
            'terms_agreed' => $this->agree_terms,
            'has_sim_a' => $this->has_sim_a,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Booking submitted successfully.');
        return redirect()->route('vehiclestatus');
    }

    public function handlePhotoUpload()
    {
        if (!$this->booking)
            return;

        // Cek apakah ada foto di antrian
        if (empty($this->collected_photos)) {
            $this->addError('collected_photos', 'Mohon upload minimal 1 foto.');
            return;
        }

        $user = Auth::user();
        $bookingId = $this->booking->vehiclebooking_id;
        $userId = $user->user_id ?? Auth::id();

        try {
            if ($this->booking->status == 'approved') {
                // LOOP SEMUA FOTO DI ANTRIAN
                foreach ($this->collected_photos as $photo) {
                    $this->uploadToLocalStorageAndSave($photo, 'before', $bookingId, $userId);
                }

                $this->booking->update(['status' => 'on_progress']);
                session()->flash('success', 'Check-out photos uploaded successfully.');

            } elseif ($this->booking->status == 'returned') {
                // LOOP SEMUA FOTO DI ANTRIAN
                foreach ($this->collected_photos as $photo) {
                    $this->uploadToLocalStorageAndSave($photo, 'after', $bookingId, $userId);
                }

                // Update status logic (sesuaikan kebutuhan)
                // $this->booking->update(['status' => 'finished']); 

                session()->flash('success', 'Check-in photos uploaded successfully.');
            }

            // Reset setelah sukses
            $this->reset(['collected_photos', 'temp_photos']);

        } catch (\Exception $e) {
            session()->flash('error', 'Upload failed: ' . $e->getMessage());
        }

        return redirect()->route('vehiclestatus');
    }

    private function combineDateTime($date, $time)
    {
        $t = $time ?: '00:00';
        return Carbon::parse("{$date} {$t}")->toDateTimeString();
    }

    private function uploadToLocalStorageAndSave($file, $type, $bookingId, $userId)
    {
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        // Generate nama unik
        $filename = "booking_{$bookingId}_{$type}_" . time() . '_' . Str::random(6) . '.' . $ext;

        $file->storeAs('vehicle_photos', $filename, 'public');

        VehicleBookingPhoto::create([
            'vehiclebooking_id' => $bookingId,
            'user_id' => $userId,
            'photo_type' => $type,
            'photo_path' => "vehicle_photos/$filename",
        ]);
    }

    private function checkAvailabilityForVehicle($vehicleId, $start, $end)
    {
        return !VehicleBooking::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['pending', 'approved', 'on_progress', 'returned'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)->where('end_at', '>', $start);
            })->exists();
    }

    public function loadAvailability()
    {
        $vehicles = $this->vehicles;
        if ($this->date_from && $this->start_time && $this->date_to && $this->end_time) {
            $start = $this->combineDateTime($this->date_from, $this->start_time);
            $end = $this->combineDateTime($this->date_to, $this->end_time);
        } else {
            $this->availability = [];
            $this->unavailableVehicleIds = [];
            return;
        }

        $this->availability = $vehicles->map(function ($v) use ($start, $end) {
            $isAvailable = $this->checkAvailabilityForVehicle($v->vehicle_id, $start, $end);
            return [
                'vehicle_id' => $v->vehicle_id,
                'label' => ($v->vehicle_name ?? $v->name) . ($v->plate_number ? " — " . $v->plate_number : ''),
                'status' => $isAvailable ? 'available' : 'unavailable',
            ];
        })->toArray();

        $this->unavailableVehicleIds = collect($this->availability)
            ->where('status', 'unavailable')
            ->pluck('vehicle_id')
            ->toArray();
    }

    public function loadRecentBookings()
    {
        $user = Auth::user();
        if (!$user)
            return;
        $this->recentBookings = VehicleBooking::with('vehicle')
            ->where('company_id', $user->company_id ?? 1)
            ->where('department_id', $user->department_id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }

    public function resetForm()
    {
        $this->booking_mode = 'perday';
        $this->date_from = Carbon::today()->format('Y-m-d');
        $this->calculateDates();
        $this->loadAvailability();
        $this->purpose = null;
        $this->destination = null;
        $this->vehicle_id = null;
        $this->has_sim_a = false;
        $this->agree_terms = false;
        $this->booking = null;
        // Reset Antrian Foto
        $this->collected_photos = [];
        $this->temp_photos = [];
    }

    public function render()
    {
        return view('livewire.pages.user.bookvehicle', [
            'booking' => $this->booking,
            'departments' => $this->departments,
            'vehicles' => $this->vehicles,
            'availability' => $this->availability,
            'recentBookings' => $this->recentBookings,
        ]);
    }
}