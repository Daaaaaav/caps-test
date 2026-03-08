<?php

namespace App\Livewire\Pages\User;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\BookingRoom;
use App\Models\Requirement;

#[Layout('layouts.app')]
#[Title('BookRoom')]
class Bookroom extends Component
{
    public string $view = 'form';
    public ?Carbon $selectedDate = null;
    public ?Carbon $currentWeek = null;

    // Form Properties
    public string $meeting_title = '';
    public $room_id = '';
    public string $date = '';
    public $number_of_attendees = '';
    public string $start_time = '';
    public string $end_time = '';
    public array $requirements = [];
    public string $special_notes = '';
    public bool $informInfo = false;

    // Modal State
    public bool $showQuickModal = false;

    public array $rooms = [];
    public array $bookings = [];
    public array $timeSlots = [];
    public array $weekDays = [];

    protected int $slotMinutes = 30;
    protected int $leadMinutes = 15;
    protected string $tz = 'Asia/Jakarta';
    public string $minStart = '00:00';

    public int $roomsPerPage = 6;
    public int $roomPage = 1;

    public array $requirementsMaster = [];
    public bool $confirmCapacityOverride = false;

    public function mount(): void
    {
        $now = Carbon::now($this->tz);
        $this->selectedDate = $now->copy();
        $this->currentWeek = $now->copy()->startOfWeek();
        $this->date = $now->toDateString();

        $start = $now->copy()->addMinutes($this->leadMinutes);
        $this->start_time = $this->roundUpToSlot($start)->format('H:i');
        $this->end_time = Carbon::createFromFormat('H:i', $this->start_time)->addMinutes($this->slotMinutes)->format('H:i');

        $this->loadRoomsFromDb();
        $this->loadRecentBookings();
        $this->buildTimeSlots();
        $this->buildWeekDays();
        $this->updateMinStart();
        $this->recalculateAvailability();
        $this->loadRequirementsForCompany();
    }

    protected function roundUpToSlot(Carbon $time): Carbon
    {
        $minute = (int) $time->minute;
        $extra = $this->slotMinutes - ($minute % $this->slotMinutes);
        if ($extra === $this->slotMinutes) $extra = 0;
        return $time->copy()->addMinutes($extra)->setSecond(0);
    }

    protected function updateMinStart(): void
    {
        if ($this->view !== 'form') {
            return;
        }

        $now = Carbon::now($this->tz);
        if ($this->date === $now->toDateString()) {
            $this->minStart = $now->format('H:i');
            // Only auto-bump time if we are NOT in the modal (to prevent modal jumping)
            if (!$this->showQuickModal && $this->start_time < $this->minStart) {
                $bumped = $this->roundUpToSlot($now->copy()->addMinutes($this->leadMinutes));
                $this->start_time = $bumped->format('H:i');
                $this->end_time = $bumped->copy()->addMinutes($this->slotMinutes)->format('H:i');
            }
        } else {
            $this->minStart = '00:00';
        }
    }

    public function switchView(string $view): void
    {
        $this->view = in_array($view, ['form', 'calendar'], true) ? $view : 'form';
        if ($this->view === 'form') {
            $this->updateMinStart();
        }
    }

    // --- Calendar Navigation ---
    public function previousWeek(): void
    {
        $this->selectedDate = Carbon::parse($this->date, $this->tz)->subWeek();
        $this->date = $this->selectedDate->toDateString();
        $this->currentWeek = $this->selectedDate->copy()->startOfWeek();
        $this->buildWeekDays();
        $this->updateMinStart();
    }

    public function nextWeek(): void
    {
        $this->selectedDate = Carbon::parse($this->date, $this->tz)->addWeek();
        $this->date = $this->selectedDate->toDateString();
        $this->currentWeek = $this->selectedDate->copy()->startOfWeek();
        $this->buildWeekDays();
        $this->updateMinStart();
    }

    public function previousMonth(): void
    {
        $this->selectedDate = Carbon::parse($this->date, $this->tz)->subMonth();
        $this->date = $this->selectedDate->toDateString();
        $this->currentWeek = $this->selectedDate->copy()->startOfWeek();
        $this->buildWeekDays();
        $this->updateMinStart();
    }

    public function nextMonth(): void
    {
        $this->selectedDate = Carbon::parse($this->date, $this->tz)->addMonth();
        $this->date = $this->selectedDate->toDateString();
        $this->currentWeek = $this->selectedDate->copy()->startOfWeek();
        $this->buildWeekDays();
        $this->updateMinStart();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->date, $this->tz)->subDay();
        $this->date = $this->selectedDate->toDateString();
        $this->updateMinStart();
        $this->recalculateAvailability();
    }

    public function nextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->date, $this->tz)->addDay();
        $this->date = $this->selectedDate->toDateString();
        $this->updateMinStart();
        $this->recalculateAvailability();
    }

    // --- Interaction: Open Modal from Calendar ---
    public function selectCalendarSlot(int $roomId, string $date, string $startTime): void
    {
        // 1. Security: Check if past
        $slotTime = Carbon::parse("$date $startTime", $this->tz);
        if ($slotTime->lt(Carbon::now($this->tz)->addMinutes($this->leadMinutes))) {
            $this->dispatch('toast', type: 'error', title: 'Unavailable', message: 'Cannot book a time slot in the past.', duration: 3000);
            return;
        }

        // 2. Pre-fill Form Data
        $this->room_id = $roomId;
        $this->date = $date;
        $this->start_time = $startTime;
        
        // Auto-calculate end time (Start + 30 mins)
        $this->end_time = Carbon::createFromFormat('H:i', $startTime)
            ->addMinutes($this->slotMinutes)
            ->format('H:i');

        // 3. Clear specific fields for new booking
        $this->meeting_title = '';
        $this->number_of_attendees = '';
        $this->requirements = [];
        $this->special_notes = '';
        $this->informInfo = false;
        $this->confirmCapacityOverride = false;

        // 4. Open Modal
        $this->showQuickModal = true;
    }

    public function closeQuickModal(): void 
    {
        $this->showQuickModal = false;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = Carbon::parse($date, $this->tz);
        $this->date = $this->selectedDate->format('Y-m-d');
        $this->updateMinStart();
        $this->recalculateAvailability();
    }

    public function updatedStartTime($val): void
    {
        if ($val) {
            $this->end_time = Carbon::createFromFormat('H:i', $val)->addMinutes($this->slotMinutes)->format('H:i');
        }
        $this->recalculateAvailability();
    }

    public function updatedEndTime(): void
    {
        $this->recalculateAvailability();
    }

    public function updatedDate(): void
    {
        $this->selectDate($this->date);
    }

    public function updatedRoomId(): void
    {
        $this->confirmCapacityOverride = false;
    }
    public function updatedNumberOfAttendees(): void
    {
        $this->confirmCapacityOverride = false;
    }

    public function nextRoomPage(): void
    {
        if ($this->roomPage < $this->totalRoomPages()) $this->roomPage++;
    }

    public function prevRoomPage(): void
    {
        if ($this->roomPage > 1) $this->roomPage--;
    }

    protected function visibleRooms(): array
    {
        $offset = ($this->roomPage - 1) * $this->roomsPerPage;
        return array_slice($this->rooms, $offset, $this->roomsPerPage);
    }

    protected function totalRoomPages(): int
    {
        return max(1, (int) ceil(count($this->rooms) / $this->roomsPerPage));
    }

    #[On('booking-created')]
    public function refreshAfterBooking(): void
    {
        $this->loadRecentBookings();
        $this->recalculateAvailability();
    }

    public function submitBooking(): void
    {
        $this->validate([
            'meeting_title' => 'required|string|min:3',
            'room_id' => 'required|integer|exists:rooms,room_id',
            'date' => 'required|date',
            'number_of_attendees' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'special_notes' => 'nullable|string|max:1000',
            'requirements' => 'array',
        ]);

        $companyId = Auth::user()->company_id;
        $now = Carbon::now($this->tz);

        if ($this->date < $now->toDateString()) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Tidak bisa booking ke tanggal yang sudah lewat.', duration: 4500);
            return;
        }
        if ($this->date === $now->toDateString() && $this->start_time < $now->format('H:i')) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Start time tidak boleh di masa lalu.', duration: 4500);
            return;
        }

        $maxCap = Room::query()
            ->where('company_id', $companyId)
            ->where('room_id', (int) $this->room_id)
            ->value('capacity');

        if ($maxCap && (int)$this->number_of_attendees > (int)$maxCap && !$this->confirmCapacityOverride) {
            $this->confirmCapacityOverride = true;
            $this->dispatch('toast', type: 'warning', title: 'Capacity mismatch', message: "Are you sure? Attendees ({$this->number_of_attendees}) exceed room max capacity ({$maxCap}). Click Submit again to proceed.", duration: 7000);
            return;
        }

        $startDt = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->start_time}", $this->tz);
        $endDt = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->end_time}", $this->tz);

        $overlap = BookingRoom::query()
            ->where('company_id', $companyId)
            ->where('room_id', $this->room_id)
            ->where('date', $this->date)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_time', '<', $endDt)
            ->where('end_time', '>', $startDt)
            ->exists();

        if ($overlap) {
            $this->dispatch('toast', type: 'error', title: 'Bentrok', message: 'Slot waktu sudah terpakai (pending/approved).', duration: 5000);
            return;
        }

        DB::transaction(function () use ($startDt, $endDt, $companyId) {
            // 1. Create the booking and capture the model instance
            $booking = BookingRoom::create([ 
                'room_id' => (int) $this->room_id,
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'department_id' => Auth::user()->department_id ?? null,
                'meeting_title' => $this->meeting_title,
                'date' => $this->date,
                'number_of_attendees' => (int) $this->number_of_attendees,
                // Use Carbon objects for saving DateTime consistency
                'start_time' => $startDt,
                'end_time' => $endDt,
                'special_notes' => $this->special_notes,
                'requestinformation' => $this->informInfo ? 'request' : null,
                'is_approve' => 0,
                'status' => 'pending',
                'approved_by' => null,
            ]);

            if (!empty($this->requirements)) {
                // 2. Get the IDs of the requirements based on their names
                $ids = Requirement::where('company_id', $companyId)
                    ->whereIn('name', $this->requirements)
                    ->pluck('requirement_id')
                    ->toArray();
                
                // 3. ATTACH the requirement IDs to the newly created booking
                if (!empty($ids)) {
                    $booking->requirements()->attach($ids); // <-- FIX APPLIED HERE
                }
            }
        });

        $this->loadRecentBookings();
        $this->recalculateAvailability();
        $this->confirmCapacityOverride = false;
        $this->showQuickModal = false; // Close modal
        $this->resetForm(true);

        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Booking tersimpan (pending approval).', duration: 4000);
    }

    protected function loadRoomsFromDb(): void
    {
        $companyId = Auth::user()->company_id;

        $this->rooms = Room::query()
            ->where('company_id', $companyId)
            ->orderBy('room_name')
            ->get(['room_id', 'room_name'])
            ->map(fn($r) => [
                'id' => (int) $r->room_id,
                'name' => (string) $r->room_name,
                'available_req' => true,
            ])->values()->all();
    }

    protected function loadRequirementsForCompany(): void
    {
        $companyId = (int) (Auth::user()->company_id ?? 0);

        $this->requirementsMaster = Requirement::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn($n) => (string) $n)
            ->values()
            ->all();
    }

    protected function loadRecentBookings(): void
    {
        $companyId = Auth::user()->company_id;

        $this->bookings = BookingRoom::query()
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['bookingroom_id', 'room_id', 'meeting_title', 'date', 'start_time', 'end_time', 'status', 'is_approve', 'requestinformation'])
            ->map(fn($b) => [
                'id' => (int) $b->bookingroom_id,
                'room_id' => (int) $b->room_id,
                'meeting_title' => (string) $b->meeting_title,
                'date' => (string) $b->date,
                'start_time' => (string) $b->start_time,
                'end_time' => (string) $b->end_time,
                'status' => (string) ($b->status),
                'is_approve' => (bool) ($b->is_approve ?? 0),
                'requestinformation' => $b->requestinformation,
            ])->all();
    }

    protected function buildTimeSlots(): void
    {
        $start = Carbon::createFromTime(8, 0, 0, $this->tz);
        $end   = Carbon::createFromTime(18, 0, 0, $this->tz);

        $slots = [];
        for ($t = $start->copy(); $t->lt($end); $t->addMinutes($this->slotMinutes)) {
            $slots[] = $t->format('H:i');
        }

        $this->timeSlots = $slots;
    }

    protected function buildWeekDays(): void
    {
        $this->weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $this->weekDays[] = $this->currentWeek->copy()->addDays($i);
        }
    }

    protected function recalculateAvailability(): void
    {
        $companyId = Auth::user()->company_id;
        $reqStart = ($this->date && $this->start_time)
            ? Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->start_time}", $this->tz) : null;
        $reqEnd = ($this->date && $this->end_time)
            ? Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->end_time}", $this->tz) : null;

        $this->rooms = collect($this->rooms)->map(function ($r) use ($reqStart, $reqEnd, $companyId) {
            $busyReq = false;
            if ($reqStart && $reqEnd) {
                $busyReq = BookingRoom::query()
                    ->where('company_id', $companyId)
                    ->where('room_id', $r['id'])
                    ->where('date', $reqStart->toDateString())
                    ->whereIn('status', ['pending', 'approved'])
                    ->where('start_time', '<', $reqEnd)
                    ->where('end_time', '>', $reqStart)
                    ->exists();
            }
            $r['available_req'] = !$busyReq;
            return $r;
        })->values()->all();
    }

    public function getBookingForSlot(int $roomId, string $ymd, string $timeSlot): ?array
    {
        $companyId = Auth::user()->company_id;
        $slotStart = Carbon::createFromFormat('Y-m-d H:i', "{$ymd} {$timeSlot}", $this->tz);
        $slotEnd = $slotStart->copy()->addMinutes($this->slotMinutes);

        $b = BookingRoom::query()
            ->where('company_id', $companyId)
            ->where('room_id', $roomId)
            ->where('date', $ymd)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_time', '<', $slotEnd)
            ->where('end_time', '>', $slotStart)
            ->orderBy('start_time')
            ->first(['bookingroom_id', 'meeting_title', 'start_time', 'end_time', 'status']);

        return $b ? [
            'id' => (int) $b->bookingroom_id,
            'meeting_title' => (string) $b->meeting_title,
            'start_time' => Carbon::parse($b->start_time)->format('H:i'),
            'end_time' => Carbon::parse($b->end_time)->format('H:i'),
            'status' => (string) ($b->status ?? 'pending'),
        ] : null;
    }

    public function render()
    {
        $this->updateMinStart();
        $this->recalculateAvailability();

        return view('livewire.pages.user.bookroom', [
            'rooms' => $this->rooms,
            'bookings' => $this->bookings,
            'timeSlots' => $this->timeSlots,
            'weekDays' => $this->weekDays,
            'visibleRooms' => $this->visibleRooms(),
            'roomsPage' => $this->roomPage,
            'roomsPerPage' => $this->roomsPerPage,
            'roomsTotalPages' => $this->totalRoomPages(),
        ]);
    }

    protected function resetForm(bool $keepDate = false): void
    {
        $d = $this->date;
        $this->meeting_title = '';
        $this->room_id = '';
        $this->number_of_attendees = '';
        $this->requirements = [];
        $this->special_notes = '';
        $this->informInfo = false;
        $this->confirmCapacityOverride = false;

        $now = Carbon::now($this->tz);
        $start = $now->copy()->addMinutes($this->leadMinutes);
        $this->start_time = $this->roundUpToSlot($start)->format('H:i');
        $this->end_time = Carbon::createFromFormat('H:i', $this->start_time)->addMinutes($this->slotMinutes)->format('H:i');

        if ($keepDate) $this->date = $d;
    }
}