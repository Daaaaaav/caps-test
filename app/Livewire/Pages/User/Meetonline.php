<?php

namespace App\Livewire\Pages\User;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\BookingRoom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('Online Meeting')]
class Meetonline extends Component
{
    public string $view = 'form';

    // Form Properties
    public string $meeting_title = '';
    public string $online_provider = '';
    public string $date = '';
    public string $start_time = '';
    public string $end_time = '';
    
    // New Property for Information Dept Request
    public bool $informInfo = false;

    // Modal State
    public bool $showQuickModal = false;

    public array $timeSlots = [];
    public array $providers = [
        ['key' => 'zoom',        'label' => 'Zoom'],
        ['key' => 'google_meet', 'label' => 'Google Meet'],
    ];

    protected string $tz = 'Asia/Jakarta';

    public function mount(): void
    {
        $now = Carbon::now($this->tz)->addMinutes(15);
        $this->date       = $now->toDateString();
        $this->start_time = $now->format('H:i');
        $this->end_time   = $now->copy()->addMinutes(30)->format('H:i');
        $this->buildTimeSlots('08:00', '18:00', 30);
    }

    public function render()
    {
        $bookings = BookingRoom::where('user_id', Auth::id())
            ->where('booking_type', 'online_meeting')
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->get();

        return view('livewire.pages.user.meetonline', compact('bookings'));
    }

    public function switchView(string $view): void
    {
        $this->view = in_array($view, ['form', 'calendar'], true) ? $view : 'form';
    }

    public function selectCalendarSlot(string $provider, string $date, string $timeLabel): void
    {
        // 1. Check if past
        $selectedTime = Carbon::parse("$date $timeLabel", $this->tz);
        if ($selectedTime->lt(Carbon::now($this->tz))) {
             $this->dispatch('toast', type: 'error', message: 'Cannot book a time in the past.');
             return;
        }

        // 2. Set Data
        $this->online_provider = $provider;
        $this->date            = $date;
        $this->start_time      = $timeLabel;
        $this->end_time        = Carbon::parse($date.' '.$timeLabel, $this->tz)->addMinutes(30)->format('H:i');
        
        // 3. Reset Form & Open Modal
        $this->meeting_title = ''; 
        $this->informInfo = false; // Reset checkbox
        $this->showQuickModal = true;
    }

    public function closeQuickModal(): void 
    {
        $this->showQuickModal = false;
    }

    public function submit(): void
    {
        $this->validate([
            'meeting_title'   => 'required|string|min:3',
            'online_provider' => 'required|in:zoom,google_meet',
            'date'            => 'required|date',
            'start_time'      => 'required|date_format:H:i',
            'end_time'        => 'required|date_format:H:i|after:start_time',
            'informInfo'      => 'boolean',
        ]);

        $now     = Carbon::now($this->tz);
        $startDt = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->start_time}", $this->tz);
        $endDt   = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->end_time}",   $this->tz);

        if ($startDt->lt($now)) {
            $this->dispatch('toast', type: 'error', message: 'Tidak bisa booking waktu yang sudah lewat.');
            return;
        }

        DB::transaction(function () use ($startDt, $endDt) {
            BookingRoom::create([
                'company_id'      => Auth::user()->company_id ?? 1,
                'user_id'         => Auth::id(),
                'department_id'   => Auth::user()->department_id ?? null,
                'meeting_title'   => $this->meeting_title,
                'date'            => $this->date,
                'start_time'      => $startDt,
                'end_time'        => $endDt,
                'booking_type'    => 'online_meeting',
                'status'          => 'pending',
                'online_provider' => $this->online_provider,
                // Map boolean true to 'request', false to null
                'requestinformation' => $this->informInfo ? 'request' : null,
            ]);
        });

        // Reset
        $preset = Carbon::now($this->tz)->addMinutes(15);
        $this->reset(['meeting_title', 'online_provider', 'informInfo']); // Reset checkbox here too
        $this->start_time = $preset->format('H:i');
        $this->end_time   = $preset->copy()->addMinutes(30)->format('H:i');
        
        $this->showQuickModal = false;

        $this->dispatch('toast', type: 'success', message: 'Permintaan online meeting dikirim.', duration: 3000);
    }

    // --- Navigation Helpers (No changes needed here) ---
    public function previousWeek(): void { $this->date = Carbon::parse($this->date, $this->tz)->subWeek()->toDateString(); }
    public function nextWeek(): void { $this->date = Carbon::parse($this->date, $this->tz)->addWeek()->toDateString(); }
    public function previousMonth(): void { $this->date = Carbon::parse($this->date, $this->tz)->subMonthNoOverflow()->toDateString(); }
    public function nextMonth(): void { $this->date = Carbon::parse($this->date, $this->tz)->addMonthNoOverflow()->toDateString(); }
    public function selectDate(string $value): void { $this->date = Carbon::parse($value, $this->tz)->toDateString(); }

    protected function buildTimeSlots(string $start = '08:00', string $end = '18:00', int $stepMinutes = 30): void
    {
        $slots = [];
        $t    = Carbon::createFromFormat('H:i', $start, $this->tz);
        $endT = Carbon::createFromFormat('H:i', $end, $this->tz);
        while ($t->lt($endT)) {
            $slots[] = $t->format('H:i');
            $t->addMinutes($stepMinutes);
        }
        $this->timeSlots = $slots;
    }

    public function getOnlineBookingForSlot(string $provider, string $date, string $timeLabel): ?array
    {
        $slotStart = Carbon::parse($date.' '.$timeLabel, $this->tz);
        $slotEnd   = (clone $slotStart)->addMinutes(30);

        $b = BookingRoom::query()
            ->where('booking_type', 'online_meeting')
            ->where('online_provider', $provider)
            ->where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->where('start_time', '<', $slotEnd)
            ->where('end_time',   '>', $slotStart)
            ->orderBy('start_time')
            ->first();

        if (!$b) return null;

        return [
            'id'                 => $b->bookingroom_id ?? $b->id ?? null,
            'meeting_title'      => $b->meeting_title,
            'start_time'         => $b->start_time,
            'end_time'           => $b->end_time,
            'status'             => $b->status,
            'online_meeting_url' => $b->online_meeting_url,
            'provider'           => $provider,
        ];
    }
}