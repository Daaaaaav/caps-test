<?php

namespace App\Livewire\Pages\Receptionist;

use App\Models\BookingRoom;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

use App\Livewire\Pages\Receptionist\Traits\HasViewMode;

#[Layout('layouts.receptionist')]
#[Title('Room Approval')]
class RoomApproval extends Component
{
    use WithPagination;
    use HasViewMode;

    protected string $paginationTheme = 'tailwind';

    public int $perPending = 6;
    public int $perOngoing = 6;

    /** Poller */
    public function tick(): void
    {
        // No action needed; Livewire will automatically re-render and re-query
    }

    private function uiMap(BookingRoom $r): array
    {
        return [
            'id' => $r->getKey(),
            'meeting_title' => $r->meeting_title,
            'room' => (string) ($r->room?->room_number ?? $r->room_id),
            'date' => $r->date ? Carbon::parse($r->date)->format('d M Y') : '—',
            'time' => $r->start_time ? Carbon::parse($r->start_time)->format('H:i') : '—',
            'time_end' => $r->end_time ? Carbon::parse($r->end_time)->format('H:i') : '—',
            'participants' => (int) ($r->number_of_attendees ?? 0),
            'status' => $r->status,
        ];
    }

    public function render()
    {
        $cid = Auth::user()?->company_id;

        // Paginated pending list
        $pending = BookingRoom::with('room')
            ->company($cid)
            ->pending()
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate($this->perPending, pageName: 'pendingPage')
            ->through(fn($r) => $this->uiMap($r));

        // Paginated ongoing list
        $ongoing = BookingRoom::with('room')
            ->company($cid)
            ->approved()
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate($this->perOngoing, pageName: 'ongoingPage')
            ->through(fn($r) => $this->uiMap($r));

        return view('livewire.pages.receptionist.room-approval', [
            'pending' => $pending,
            'ongoing' => $ongoing,
        ]);
    }
}
