<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\BookingRoom;
use App\Models\Room;
use Carbon\Carbon;

#[Layout('layouts.receptionist')]
#[Title('Booking History')]
class BookingHistory extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public int $perDone     = 5;
    public int $perRejected = 5;

    public bool $withTrashed = false;

    // unified search
    public string $q = '';

    public ?string $selectedDate = null;   // 'YYYY-MM-DD'
    public string $dateMode      = 'semua'; // 'semua' | 'terbaru' | 'terlama'

    // Online/Offline scope: all | offline | online
    public string $typeScope = 'all';

    public bool $showModal   = false;
    public string $modalMode = 'create';
    public ?int $editingId   = null;

    /** @var array<int,array{id:int,name:string}> */
    public array $rooms = [];

    /** room filter (for sidebar + list) */
    public ?int $roomFilterId = null;

    /** @var array<int,array{id:int,label:string}> */
    public array $roomsOptions = [];

    /** mobile filter modal */
    public bool $showFilterModal = false;

    public array $form = [
        'booking_type'    => 'meeting',
        'meeting_title'   => '',
        'date'            => '',
        'start_time'      => '',
        'end_time'        => '',
        'room_id'         => null,
        'online_provider' => null,
        'notes'           => '',
        'status'          => 'completed',
        'book_reject'     => '',     // ⬅️ reason (required if rejected)
    ];

    // Tabs: done | rejected
    public string $activeTab = 'done';

    // Safer sets (normalized)
    private const DONE_SET     = ['done', 'completed', '3'];
    private const REJECTED_SET = ['rejected', '2'];

    protected string $tz = 'Asia/Jakarta';

    public function mount(): void
    {
        $this->rooms = Room::select('room_id', 'room_name')
            ->orderBy('room_name')
            ->get()
            ->map(fn ($r) => [
                'id'   => (int) $r->room_id,
                'name' => (string) $r->room_name,
            ])
            ->toArray();

        $this->roomsOptions = collect($this->rooms)
            ->map(fn (array $r) => [
                'id'    => $r['id'],
                'label' => $r['name'],
            ])
            ->values()
            ->all();
    }

    private function normStatus(mixed $v): string
    {
        return strtolower(trim((string) $v));
    }

    /**
     * Auto-progress approved → completed ketika end datetime lewat.
     */
    private function autoProgressToDone(): int
    {
        $now = Carbon::now($this->tz)->toDateTimeString();

        $endExpr = "COALESCE(
            CASE WHEN `end_time` REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} ' THEN `end_time` END,
            CASE WHEN `date`     REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2} ' THEN `date` END,
            CONCAT(`date`, ' ', `end_time`)
        )";

        return DB::transaction(function () use ($now, $endExpr) {
            return BookingRoom::query()
                ->whereRaw("$endExpr IS NOT NULL")
                ->whereRaw("$endExpr <= ?", [$now])

                // Only auto-complete items that are APPROVED
                ->where(function ($q) {
                    $q->whereRaw("LOWER(TRIM(`status`)) = 'approved'");
                })

                // Extra guard: if someone wrote a reject reason, do NOT move to done
                ->where(function ($q) {
                    $q->whereNull('book_reject')
                      ->orWhere('book_reject', '');
                })

                ->update([
                    'status'     => 'completed',
                    'updated_at' => Carbon::now($this->tz)->toDateTimeString(),
                ]);
        });
    }

    // ───────── Tabs ─────────

    public function setTab(string $tab): void
    {
        if (!in_array($tab, ['done', 'rejected'], true)) {
            return;
        }

        $this->activeTab = $tab;

        // reset both paginations for safety
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    /**
     * Scope online/offline/all.
     */
    public function setTypeScope(string $scope): void
    {
        if (!in_array($scope, ['all', 'offline', 'online'], true)) {
            return;
        }

        $this->typeScope = $scope;
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    // ───────── Pagination reset on filter changes ─────────

    public function updatedQ(): void
    {
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    public function updatedWithTrashed(): void
    {
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    public function updatedSelectedDate(): void
    {
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    public function updatedDateMode(): void
    {
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    // ───────── Room filter helpers ─────────

    public function selectRoom(int $roomId): void
    {
        $this->roomFilterId = $roomId;
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
        $this->showFilterModal = false;
    }

    public function clearRoomFilter(): void
    {
        $this->roomFilterId = null;
        $this->resetPage('pageDone');
        $this->resetPage('pageRejected');
    }

    public function openFilterModal(): void
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal(): void
    {
        $this->showFilterModal = false;
    }

    // ───────── CRUD & modal ─────────

    public function create(string $bookingType = 'meeting', string $status = 'completed'): void
    {
        $this->modalMode = 'create';
        $this->editingId = null;

        $now = Carbon::now($this->tz);

        $this->form = [
            'booking_type'    => $bookingType,
            'meeting_title'   => '',
            'date'            => $now->toDateString(),
            'start_time'      => $now->format('H:00'),
            'end_time'        => $now->copy()->addHour()->format('H:00'),
            'room_id'         => null,
            'online_provider' => in_array($bookingType, ['online_meeting', 'onlinemeeting'], true)
                ? 'zoom'
                : null,
            'notes'           => '',
            'status'          => $status,
            'book_reject'     => '', // ⬅️ NEW
        ];

        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $row = $this->baseQuery()->withTrashed()->findOrFail($id);

        $this->modalMode = 'edit';
        $this->editingId = $row->getKey();

        $this->form = [
            'booking_type'    => (string) ($row->booking_type ?? 'meeting'),
            'meeting_title'   => (string) ($row->meeting_title ?? ''),
            'date'            => (string) ($row->date ?? ''),
            'start_time'      => (string) ($row->start_time ?? ''),
            'end_time'        => (string) ($row->end_time ?? ''),
            'room_id'         => $row->room_id,
            'online_provider' => (string) ($row->online_provider ?? ''),
            'notes'           => (string) ($row->notes ?? ''),
            'status'          => $this->normalizeDbStatus($row->status),
            'book_reject'     => (string) ($row->book_reject ?? ''), // ⬅️ NEW
        ];

        $this->showModal = true;
    }

    public function updatedFormStatus($value): void
    {
        // UX: clear reason when not rejected
        if ($value !== 'rejected') {
            $this->form['book_reject'] = '';
        }
    }

    public function save(): void
    {
        $data        = $this->validateForm();
        $statusForDb = $data['status'];

        if ($this->modalMode === 'create') {
            BookingRoom::create([
                'booking_type'    => $data['booking_type'],
                'meeting_title'   => $data['meeting_title'],
                'date'            => $data['date'],
                'start_time'      => $data['start_time'],
                'end_time'        => $data['end_time'],
                'room_id'         => in_array($data['booking_type'], ['meeting', 'bookingroom'], true)
                    ? $data['room_id']
                    : null,
                'online_provider' => in_array($data['booking_type'], ['online_meeting', 'onlinemeeting'], true)
                    ? $data['online_provider']
                    : null,
                'notes'           => $data['notes'],
                'status'          => $statusForDb,
                'book_reject'     => $statusForDb === 'rejected' ? ($data['book_reject'] ?? null) : null, // ⬅️ NEW
                'user_id'         => Auth::id(),
            ]);
        } else {
            $row = $this->baseQuery()->withTrashed()->findOrFail($this->editingId);

            $row->update([
                'booking_type'    => $data['booking_type'],
                'meeting_title'   => $data['meeting_title'],
                'date'            => $data['date'],
                'start_time'      => $data['start_time'],
                'end_time'        => $data['end_time'],
                'room_id'         => in_array($data['booking_type'], ['meeting', 'bookingroom'], true)
                    ? $data['room_id']
                    : null,
                'online_provider' => in_array($data['booking_type'], ['online_meeting', 'onlinemeeting'], true)
                    ? $data['online_provider']
                    : null,
                'notes'           => $data['notes'],
                'status'          => $statusForDb,
                'book_reject'     => $statusForDb === 'rejected' ? ($data['book_reject'] ?? null) : null, // ⬅️ NEW
            ]);
        }

        $this->showModal = false;
        $this->dispatch('toast', type: 'success', title: 'Disimpan', message: 'Booking berhasil disimpan.', duration: 3000);

        if ($statusForDb === 'completed') {
            $this->resetPage('pageDone');
        }
        if ($statusForDb === 'rejected') {
            $this->resetPage('pageRejected');
        }
    }

    public function destroy(int $id): void
    {
        $row = $this->baseQuery()->findOrFail($id);
        $row->delete();

        $this->dispatch('toast', type: 'success', title: 'Dihapus', message: 'Booking dihapus.', duration: 3000);
        $this->fixEmptyPageAfterChange();
    }

    public function restore(int $id): void
    {
        $row = $this->baseQuery()->onlyTrashed()->findOrFail($id);
        $row->restore();

        $this->dispatch('toast', type: 'success', title: 'Dipulihkan', message: 'Booking dipulihkan.', duration: 3000);
        $this->fixEmptyPageAfterChange();
    }

    public function forceDestroy(int $id): void
    {
        $row = $this->baseQuery()->withTrashed()->findOrFail($id);
        $row->forceDelete();

        $this->fixEmptyPageAfterChange();
        $this->dispatch('toast', type: 'success', title: 'Dihapus Permanen', message: 'Booking dihapus permanen.', duration: 3000);
    }

    private function fixEmptyPageAfterChange(): void
    {
        $done = $this->getDoneRowsProperty();
        if ($done->isEmpty() && $done->currentPage() > 1) {
            $this->setPage($done->currentPage() - 1, 'pageDone');
        }

        $rej = $this->getRejectedRowsProperty();
        if ($rej->isEmpty() && $rej->currentPage() > 1) {
            $this->setPage($rej->currentPage() - 1, 'pageRejected');
        }
    }

    private function baseQuery()
    {
        return BookingRoom::query()->with('room');
    }

    private function normalizeDbStatus($status): string
    {
        $s = $this->normStatus($status);

        if (in_array($s, self::DONE_SET, true)) {
            return 'completed';
        }
        if (in_array($s, self::REJECTED_SET, true)) {
            return 'rejected';
        }

        return $s ?: 'completed';
    }

    private function validateForm(): array
    {
        $isRoomType = in_array($this->form['booking_type'] ?? null, ['meeting', 'bookingroom'], true);

        $rules = [
            'form.booking_type'    => ['required', 'string', 'max:50'],
            'form.meeting_title'   => ['required', 'string', 'max:255'],
            'form.date'            => ['nullable', 'date'],
            'form.start_time'      => ['nullable', 'string', 'max:10'],
            'form.end_time'        => ['nullable', 'string', 'max:10'],
            'form.room_id'         => [$isRoomType ? 'required' : 'nullable', 'integer', 'exists:rooms,room_id'],
            'form.online_provider' => [$isRoomType ? 'nullable' : 'required', Rule::in(['zoom', 'google_meet'])],
            'form.notes'           => ['nullable', 'string', 'max:1000'],
            'form.status'          => ['required', Rule::in(['completed', 'rejected'])],
            // required if rejected
            'form.book_reject'     => ['nullable', 'string', 'max:500', 'required_if:form.status,rejected'],
        ];

        $data = $this->validate($rules)['form'];

        foreach (['date', 'start_time', 'end_time', 'notes', 'book_reject'] as $k) {
            if (($data[$k] ?? null) === '') {
                $data[$k] = null;
            }
        }

        return $data;
    }

    // ───────── Query accessors used by Blade ─────────

    public function getDoneRowsProperty()
    {
        $q = $this->baseQuery()
            ->when(!$this->withTrashed, fn ($qq) => $qq->whereNull('deleted_at'))
            ->when($this->withTrashed,  fn ($qq) => $qq->withTrashed())

            // Only rows whose normalized status is in DONE_SET
            ->where(function ($qq) {
                $qq->whereIn(DB::raw("LOWER(TRIM(`status`))"), self::DONE_SET);
            })

            // Never show anything that carries a rejection reason
            ->where(function ($qq) {
                $qq->whereNull('book_reject')
                   ->orWhere('book_reject', '');
            })

            // Room filter
            ->when($this->roomFilterId, fn ($qq) => $qq->where('room_id', $this->roomFilterId))

            // Type scope filter: online / offline / all
            ->when($this->typeScope === 'online', function ($qq) {
                $qq->whereIn('booking_type', ['online_meeting', 'onlinemeeting']);
            })
            ->when($this->typeScope === 'offline', function ($qq) {
                $qq->where(function ($q) {
                    $q->whereNull('booking_type')
                      ->orWhereNotIn('booking_type', ['online_meeting', 'onlinemeeting']);
                });
            })

            // Other filters
            ->when($this->q !== '',               fn ($qq) => $qq->where('meeting_title', 'like', '%' . $this->q . '%'))
            ->when($this->selectedDate,           fn ($qq) => $qq->whereDate('date', $this->selectedDate))
            ->when($this->dateMode === 'terbaru', fn ($qq) => $qq->orderByDesc('date')->orderByDesc('start_time'))
            ->when($this->dateMode === 'terlama', fn ($qq) => $qq->orderBy('date')->orderBy('start_time'))
            ->when($this->dateMode === 'semua',   fn ($qq) => $qq->orderByRaw("COALESCE(`date`, '0000-01-01') DESC")
                                                                 ->orderByRaw("COALESCE(`start_time`, '00:00:00') DESC"));

        return $q->paginate($this->perDone, ['*'], 'pageDone');
    }

    public function getRejectedRowsProperty()
    {
        $q = $this->baseQuery()
            ->when(!$this->withTrashed, fn ($qq) => $qq->whereNull('deleted_at'))
            ->when($this->withTrashed,  fn ($qq) => $qq->withTrashed())

            // Normalized check for "rejected"
            ->whereRaw("LOWER(TRIM(`status`)) = 'rejected'")

            // Room filter
            ->when($this->roomFilterId, fn ($qq) => $qq->where('room_id', $this->roomFilterId))

            // Type scope filter: online / offline / all
            ->when($this->typeScope === 'online', function ($qq) {
                $qq->whereIn('booking_type', ['online_meeting', 'onlinemeeting']);
            })
            ->when($this->typeScope === 'offline', function ($qq) {
                $qq->where(function ($q) {
                    $q->whereNull('booking_type')
                      ->orWhereNotIn('booking_type', ['online_meeting', 'onlinemeeting']);
                });
            })

            // Other filters
            ->when($this->q !== '',               fn ($qq) => $qq->where('meeting_title', 'like', '%' . $this->q . '%'))
            ->when($this->selectedDate,           fn ($qq) => $qq->whereDate('date', $this->selectedDate))
            ->when($this->dateMode === 'terbaru', fn ($qq) => $qq->orderByDesc('date')->orderByDesc('start_time'))
            ->when($this->dateMode === 'terlama', fn ($qq) => $qq->orderBy('date')->orderBy('start_time'))
            ->when($this->dateMode === 'semua',   fn ($qq) => $qq->orderByDesc('date')->orderByDesc('start_time'));

        return $q->paginate($this->perRejected, ['*'], 'pageRejected');
    }

    /**
     * Recent completed for sidebar + mobile.
     */
    public function getRecentCompletedProperty()
    {
        return $this->baseQuery()
            ->whereIn(DB::raw("LOWER(TRIM(`status`))"), self::DONE_SET)
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        $this->autoProgressToDone();

        return view('livewire.pages.receptionist.booking-history', [
            'doneRows'        => $this->doneRows,
            'rejectedRows'    => $this->rejectedRows,
            'rooms'           => $this->rooms,
            'roomsOptions'    => $this->roomsOptions,
            'recentCompleted' => $this->recentCompleted,
            'roomFilterId'    => $this->roomFilterId,
            'showFilterModal' => $this->showFilterModal,
        ]);
    }
}
