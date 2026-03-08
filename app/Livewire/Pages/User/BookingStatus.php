<?php

namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BookingRoom;
use App\Models\Room;

#[Layout('layouts.app')]
#[Title('Room Booking')]
class BookingStatus extends Component
{
    use WithPagination;

    public string $q = '';
    public ?int $roomFilter = null;
    public string $dbStatusFilter = 'all';
    public string $sortFilter = 'recent';
    public string $mode = 'meeting';

    public int $perPage = 10;
    protected string $tz = 'Asia/Jakarta';

    protected $queryString = [
        'q'              => ['except' => ''],
        'roomFilter'     => ['except' => null],
        'dbStatusFilter' => ['except' => 'all'],
        'sortFilter'     => ['except' => 'recent'],
        'mode'           => ['except' => 'meeting'],
        'page'           => ['except' => 1],
    ];

    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['meeting', 'online']) ? $mode : 'meeting';
        $this->resetPage();
    }

    public function updatingQ()          { $this->resetPage(); }
    public function updatingRoomFilter() { $this->resetPage(); }
    public function updatingDbStatusFilter() { $this->resetPage(); }
    public function updatingSortFilter() { $this->resetPage(); }

    public function render()
    {
        $user      = Auth::user();
        $userId    = $user?->user_id ?? Auth::id();
        $companyId = (int) ($user?->company_id ?? 0);
        $now       = Carbon::now($this->tz);

        $query = BookingRoom::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereIn('booking_type', $this->mode === 'online'
                ? ['online_meeting']
                : ['meeting', 'bookingroom']
            );

        // search
        if (strlen(trim($this->q)) > 0) {
            $q = trim($this->q);
            $query->where(function ($qq) use ($q, $companyId) {
                $qq->where('meeting_title', 'like', "%{$q}%");
                if ($this->mode === 'meeting') {
                    $roomIds = Room::where('company_id', $companyId)
                        ->where('room_name', 'like', "%{$q}%")
                        ->pluck('room_id')
                        ->all();
                    if (!empty($roomIds)) {
                        $qq->orWhereIn('room_id', $roomIds);
                    }
                } else {
                    $qq->orWhere('online_provider', 'like', "%{$q}%");
                }
            });
        }

        if ($this->mode === 'meeting' && $this->roomFilter) {
            $query->where('room_id', $this->roomFilter);
        }

        if ($this->dbStatusFilter !== 'all') {
            $query->where('status', $this->dbStatusFilter);
        }

        switch ($this->sortFilter) {
            case 'oldest':
                $query->orderBy('start_time', 'asc');
                break;
            case 'nearest':
                $query->orderByRaw('CASE WHEN start_time >= ? THEN 0 ELSE 1 END', [$now->toDateTimeString()])
                      ->orderBy('start_time', 'asc');
                break;
            default:
                $query->orderBy('start_time', 'desc');
                break;
        }

        $bookings = $query->paginate($this->perPage);

        // 👇 use room_name (your table)
        $roomMap = Room::where('company_id', $companyId)
            ->pluck('room_name', 'room_id')
            ->toArray();

        $rooms = Room::where('company_id', $companyId)
            ->orderBy('room_name')
            ->get(['room_id', 'room_name']);

        return view('livewire.pages.user.bookingstatus', compact('bookings', 'roomMap', 'rooms'));
    }
}
