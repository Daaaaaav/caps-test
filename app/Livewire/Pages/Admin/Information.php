<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use Carbon\Carbon;

use App\Models\Information as InformationModel;
use App\Models\BookingRoom;
use App\Models\Department;
use App\Models\User; // Import User model

#[Layout('layouts.admin')]
#[Title('Admin - Information')]
class Information extends Component
{
    use WithPagination;

    // ====== UI STATE ======
    public string $mode = 'index'; // index|create|edit
    public ?int $editingId = null;

    // ====== PAGINATION ======
    protected string $paginationTheme = 'tailwind';
    public int $perPageInfo = 12;  // information list
    public int $perPageReq  = 3;   // requests (offline/online)

    // ====== FILTERS ======
    public ?string $search = null;

    // ====== FORM FIELDS (used by Create/Edit Modal) ======
    public string $description = '';
    public string $event_at   = ''; // Y-m-d\TH:i

    // ====== REQUEST→INFORM MODAL (UPDATED FOR EDITING) ======
    public bool  $informModal = false;
    public ?int  $informBookingId = null;
    public ?string $informDescription = null; // Holds the editable description

    // ====== HEADER & DEPT SWITCHER ======
    public string $company_name    = '-';
    public string $department_name = '-';
    public array  $deptOptions     = [];
    public ?int   $selected_department_id = null;
    public ?int   $primary_department_id  = null;
    public bool $showSwitcher = false;

    // **[NEW PROPERTY]** To store the superadmin status
    public bool $is_superadmin = false;

    // ====== REJECT MODAL ======
    public bool $rejectModal = false;
    public ?int $rejectBookingId = null;
    public string $rejectionReason = '';

    // --- [NEW METHOD] Helper to check for Superadmin role ---
    /**
     * Checks if the user has the 'Superadmin' role.
     */
    private function isSuperAdmin(User $user): bool
    {
        // **ADJUST THIS LINE BASED ON YOUR ACTUAL USER/ROLE STRUCTURE**
        // Example assumes a 'role' relation with a 'name' property
        return optional($user->role)->name === 'Superadmin';
    }

    public function mount(): void
    {
        try {
            $auth = Auth::user()->loadMissing(['company', 'department', 'role']); // Load role
            $this->is_superadmin = $this->isSuperAdmin($auth); // Set the flag

            $this->company_name = optional($auth->company)->company_name ?? '-';
            $this->primary_department_id = $auth->department_id ?: null;

            $this->loadUserDepartments();

            if (!$this->selected_department_id) {
                $this->selected_department_id = $this->primary_department_id
                    ?: ($this->deptOptions[0]['id'] ?? null);
            }

            // **[CHANGE]** Display logic for Superadmin
            if ($this->is_superadmin) {
                // Add "View All" option and default selection to null
                $this->department_name = 'SEMUA DEPARTEMEN';
                $this->selected_department_id = null; 
                if (!in_array(null, array_column($this->deptOptions, 'id'))) {
                    array_unshift($this->deptOptions, ['id' => null, 'name' => 'SEMUA DEPARTEMEN']);
                }
            } else {
                $this->department_name = $this->resolveDeptName($this->selected_department_id);
            }

            $this->resetForm();
            $this->resetBroadcastForm(); // Assuming this was for a separate broadcast component
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal memuat header.', duration: 5000);
        }
    }

    protected function loadUserDepartments(): void
    {
        $user = Auth::user();

        // **[CHANGE]** If Superadmin, load ALL departments for the company
        if ($this->is_superadmin) {
             $rows = Department::where('company_id', $user->company_id)
                ->orderBy('department_name')
                ->get(['department_id as id', 'department_name as name']);
        } else {
            // Original logic for non-Superadmin
            $rows = DB::table('user_departments as ud')
                ->join('departments as d', 'd.department_id', '=', 'ud.department_id')
                ->where('ud.user_id', $user->user_id)
                ->orderBy('d.department_name')
                ->get(['d.department_id as id', 'd.department_name as name']);
        }

        $this->deptOptions = $rows->map(fn($r) => ['id' => (int)$r->id, 'name' => (string)$r->name])->values()->all();

        // Add the primary department if not already included
        $primaryId = $user->department_id;
        $isPrimaryInList = collect($this->deptOptions)->contains('id', $primaryId);

        if ($primaryId && !$isPrimaryInList) {
             // Use array_unshift to add the primary department to the beginning of the array
             $primaryName = Department::where('department_id', $primaryId)->value('department_name') ?? 'Unknown';
             array_unshift($this->deptOptions, ['id' => (int)$primaryId, 'name' => (string)$primaryName]);
        }
        
        $this->showSwitcher = count($this->deptOptions) > 1 || $this->is_superadmin;


        if (!$this->is_superadmin && empty($this->deptOptions) && $this->primary_department_id) {
            $name = Department::where('department_id', $this->primary_department_id)->value('department_name') ?? 'Unknown';
            $this->deptOptions = [['id' => (int)$this->primary_department_id, 'name' => (string)$name]];
            $this->showSwitcher = false;
        }
    }

    protected function resolveDeptName(?int $deptId): string
    {
        if (!$deptId) return 'SEMUA DEPARTEMEN'; // Updated for Superadmin "View All"
        foreach ($this->deptOptions as $opt) {
            if ($opt['id'] === (int)$deptId) {
                return $opt['name'];
            }
        }
        return Department::where('department_id', $deptId)->value('department_name') ?? '-';
    }

    public function updatedSelectedDepartment_id(): void
    {
        $this->updatedSelectedDepartmentId();
    }

    public function updatedSelectedDepartmentId(): void
    {
        $id = $this->selected_department_id; // Keep as null if "View All" is selected

        // If not superadmin, ensure selected ID is valid
        if (!$this->is_superadmin && $id !== null) {
            $id = (int) $id;
            $allowed = collect($this->deptOptions)->pluck('id')->all();
            if (!in_array($id, $allowed, true)) {
                $this->selected_department_id = $this->primary_department_id ?: ($this->deptOptions[0]['id'] ?? null);
                $id = $this->selected_department_id;
            }
        }
        // Ensure that if 'View All' is selected (null), we update the name correctly
        if ($this->is_superadmin && $id === null) {
            $this->department_name = 'SEMUA DEPARTEMEN';
        } else {
            $this->department_name = $this->resolveDeptName($id);
        }

        $this->resetPaginationForAll();
    }

    protected function currentDeptId(): ?int
    {
        // For Superadmin, if they select 'View All' (null), return null.
        // For others, return selected_department_id (which should be non-null after validation)
        if ($this->is_superadmin && $this->selected_department_id === null) {
            return null;
        }
        return $this->selected_department_id ?: $this->primary_department_id;
    }

    protected function resetPaginationForAll(): void
    {
        $this->resetPage('infoPage');
        $this->resetPage('offlinePage');
        $this->resetPage('onlinePage');
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->description = '';
        $this->event_at    = now()->format('Y-m-d\TH:i');
    }

    private function resetBroadcastForm(): void
    {
        // Keeping this for consistency, assuming a separate broadcast feature exists
        $this->broadcast_description = '';
        $this->broadcast_event_at    = now()->format('Y-m-d\TH:i');
    }

    private function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'event_at'    => ['required', 'date'],
        ];
    }

    // ========= Information CRUD (Modal) =========

    public function openCreateEditModal(string $mode, ?int $id = null): void
    {
        $this->resetForm();
        $this->mode = $mode;

        if ($mode === 'edit' && $id) {
            $user = Auth::user();
            $deptId = $this->currentDeptId();

            try {
                $query = InformationModel::where('information_id', $id)
                    ->where('company_id', $user->company_id);
                    
                // **[CHANGE]** Only filter by department if a specific department is selected
                if ($deptId !== null) {
                    $query->where('department_id', $deptId);
                }

                $row = $query->firstOrFail();

                $this->editingId   = $row->information_id;
                $this->description = (string) $row->description;
                $this->event_at    = optional($row->event_at)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
            } catch (Throwable $e) {
                $this->dispatch('toast', type: 'error', title: 'Error', message: 'Data tidak ditemukan atau tidak memiliki akses.', duration: 5000);
                $this->cancel();
            }
        }
    }

    public function store(): void
    {
        $data = $this->validate($this->rules());
        $user = Auth::user();
        $deptId = $this->currentDeptId();

        // Information must always be tied to a specific department, even for superadmin
        if (!$deptId) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Silakan pilih departemen yang akan menerima informasi.', duration: 5000);
            return;
        }

        InformationModel::create([
            'company_id'    => $user->company_id,
            'department_id' => $deptId,
            'description'   => $data['description'],
            'event_at'      => Carbon::parse($data['event_at']),
        ]);

        $this->dispatch('toast', type: 'success', title: 'Created', message: 'Information created.', duration: 3500);
        $this->mode = 'index';
        $this->resetForm();
        $this->resetPage('infoPage');
    }

    public function update(): void
    {
        $this->validate($this->rules());

        $user   = Auth::user();
        $deptId = $this->currentDeptId();
        
        if (!$deptId) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Silakan pilih departemen yang akan menerima informasi.', duration: 5000);
            return;
        }

        $query = InformationModel::where('information_id', $this->editingId)
            ->where('company_id', $user->company_id);
            
        // **[CHANGE]** Only filter by department if a specific department is selected
        if ($deptId !== null) {
            $query->where('department_id', $deptId);
        }

        $row = $query->firstOrFail();

        $row->fill([
            'description'   => $this->description,
            'event_at'      => Carbon::parse($this->event_at),
        ])->save();

        $this->dispatch('toast', type: 'success', title: 'Updated', message: 'Information updated.', duration: 3500);
        $this->mode = 'index';
        $this->resetForm();
        $this->resetPage('infoPage');
    }

    public function destroy(int $id): void
    {
        $user   = Auth::user();
        $deptId = $this->currentDeptId();

        try {
            $query = InformationModel::where('information_id', $id)
                ->where('company_id', $user->company_id);
                
            // **[CHANGE]** Only filter by department if a specific department is selected
            if ($deptId !== null) {
                $query->where('department_id', $deptId);
            }

            $row = $query->firstOrFail();
            $row->delete();

            $this->dispatch('toast', type: 'success', title: 'Deleted', message: 'Information deleted.', duration: 3500);
            $this->resetPage('infoPage');
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal menghapus data.', duration: 5000);
        }
    }

    public function cancel(): void
    {
        $this->mode = 'index';
        $this->resetForm();
        $this->resetBroadcastForm();
        $this->informModal = false;
        $this->informBookingId = null;
        $this->informDescription = null; // Reset the editable field on close
    }

    // ========= Inform Modal Logic (EDITABLE) =========

    public function openInformModal(int $bookingId): void
    {
        try {
            $booking = BookingRoom::query()
                ->with(['user', 'room', 'department'])
                ->where('bookingroom_id', $bookingId)
                ->firstOrFail();

            $this->informBookingId = $bookingId;
            // Populate the editable description property
            $this->informDescription = $this->composeDescription($booking); 
            $this->informModal = true;
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal memuat detail booking.', duration: 5000);
        }
    }

    public function closeInformModal(): void
    {
        $this->informModal = false;
        $this->informBookingId = null;
        $this->informDescription = null;
    }

    public function submitInform(): void
    {
        // Validate the editable field
        $this->validate([
            'informBookingId' => 'required|integer',
            'informDescription' => 'required|string|min:10', // Ensure edited content is valid
        ]);

        $user    = Auth::user();
        $deptId  = $this->currentDeptId();

        // Information must always be tied to a specific department, even for superadmin
        if (!$deptId) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Silakan pilih departemen yang akan menerima informasi.', duration: 5000);
            return;
        }

        $booking = BookingRoom::query()
            ->with(['user', 'room', 'department'])
            ->where('bookingroom_id', $this->informBookingId)
            ->firstOrFail();

        $companyId = $user->company_id;

        $date = Carbon::parse($booking->date)->toDateString();
        $time = Carbon::parse($booking->start_time)->format('H:i:s');
        $eventAt = Carbon::createFromFormat('Y-m-d H:i:s', "$date $time")->format('Y-m-d H:i:s');

        // Use the potentially edited description from the modal
        $desc = $this->informDescription; 

        try {
            DB::transaction(function () use ($companyId, $deptId, $eventAt, $desc, $booking) {
                InformationModel::create([
                    'company_id'    => $companyId,
                    'department_id' => $deptId,
                    'description'   => $desc, // SAVING THE EDITED CONTENT
                    'event_at'      => $eventAt,
                ]);
                $booking->requestinformation = 'inform';
                $booking->save();
            });

            $this->closeInformModal();
            $this->resetPage('offlinePage');
            $this->resetPage('onlinePage');
            $this->dispatch('toast', type: 'success', title: 'Sent', message: 'Information dikirim ke departemen terpilih.', duration: 3500);
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Error', message: 'Gagal mengirim informasi.', duration: 5000);
        }
    }

    protected function composeDescription(BookingRoom $b): string
    {
        // ... (original composeDescription implementation)
        $title  = $b->meeting_title ?: 'Meeting';
        $date   = Carbon::parse($b->date)->translatedFormat('d M Y');
        $start  = Carbon::parse($b->start_time)->format('H:i');
        $end    = Carbon::parse($b->end_time)->format('H:i');
        $by     = optional($b->user)->name ?: 'Unknown';
        $dept   = optional($b->department)->department_name ?: '-';
        $notes  = trim((string) $b->special_notes) ?: '-';
        $attend = (string) ($b->number_of_attendees ?? '-');

        $base = [
            "Tanggal/Jam : {$date} {$start}-{$end}",
            "Requester   : {$by} (Dept: {$dept})",
            "Peserta     : {$attend}",
            "Catatan     : {$notes}",
        ];

        if ($b->booking_type === 'online_meeting') {
            $provider = strtoupper((string) $b->online_provider ?: '-');
            $url      = trim((string) $b->online_meeting_url ?: '-');
            $code     = trim((string) $b->online_meeting_code ?: '-');
            $pass     = trim((string) $b->online_meeting_password ?: '-');

            return implode("\n", array_merge(["{$title} — ONLINE MEETING"], [
                $base[0], $base[1], $base[2],
                "Provider    : {$provider}",
                "Join Link   : {$url}",
                "Meeting Code: {$code}",
                "Password    : {$pass}",
                $base[3]
            ]));
        }

        $room = optional($b->room)->room_name ?: 'Room';
        return implode("\n", array_merge(["{$title} — OFFLINE MEETING"], [
            $base[0],
            "Ruangan     : {$room}",
            $base[1], $base[2], $base[3]
        ]));
    }

    // ========= Reject Modal Logic (No change) =========

    public function openRejectModal(int $bookingId): void
    {
        $this->rejectBookingId = $bookingId;
        $this->rejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->rejectModal = false;
        $this->rejectBookingId = null;
        $this->rejectionReason = '';
    }

    public function submitReject(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:10',
        ]);

        $booking = BookingRoom::find($this->rejectBookingId);
        if ($booking) {
            DB::transaction(function () use ($booking) {
                $booking->status = 'rejected';
                $booking->book_reject = $this->rejectionReason;
                $booking->save();
            });

            $this->closeRejectModal();

            $this->dispatch('toast', type: 'success', title: 'Rejected', message: 'Booking request rejected.', duration: 3500);
            $this->resetPage('offlinePage');
            $this->resetPage('onlinePage');
        }
    }

    // ========= RENDER (Filter Information List) =========
    public function render()
    {
        $user      = Auth::user();
        $companyId = $user->company_id;
        $deptId    = $this->currentDeptId();
        
        // Offline Requests (Filtering remains the same as they are requests requiring action)
        $offline = BookingRoom::query()
            ->with(['room', 'user', 'department'])
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->where('booking_type', 'meeting')
            ->where('requestinformation', 'request')
            ->where('status', 'approved')
            // **[NOTE]** Assuming requests should still be filtered by the currently selected department for action
            ->when($deptId !== null && !$this->is_superadmin, fn($q) => $q->where('department_id', $deptId)) 
            ->orderByDesc('date')->orderByDesc('start_time')
            ->paginate($this->perPageReq, ['*'], 'offlinePage');

        // Online Requests (Filtering remains the same as they are requests requiring action)
        $online = BookingRoom::query()
            ->with(['room', 'user', 'department'])
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->where('booking_type', 'online_meeting')
            ->where('requestinformation', 'request')
            ->where('status', 'approved')
            // **[NOTE]** Assuming requests should still be filtered by the currently selected department for action
            ->when($deptId !== null && !$this->is_superadmin, fn($q) => $q->where('department_id', $deptId))
            ->orderByDesc('date')->orderByDesc('start_time')
            ->paginate($this->perPageReq, ['*'], 'onlinePage');

        // **[CHANGE]** Information List (Superadmin can see all)
        $rows = InformationModel::query()
            ->where('company_id', $companyId)
            // Only apply department filter if $deptId is NOT null (i.e., not "View All")
            ->when($deptId, fn($q) => $q->where('department_id', $deptId))
            ->when(
                $this->search,
                fn($q) =>
                $q->where('description', 'like', '%' . $this->search . '%')
            )
            ->orderByDesc('event_at')
            ->paginate($this->perPageInfo, ['*'], 'infoPage');

        return view('livewire.pages.admin.information', [
            'offline' => $offline,
            'online'  => $online,
            'rows'    => $rows,
        ]);
    }
}