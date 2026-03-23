<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

// Aliases Model
use App\Models\BookingRoom as BR;
use App\Models\Room as RM;
use App\Models\Department as DP;
use App\Models\Requirement;

#[Layout('layouts.superadmin')]
#[Title('Booking Room Management')] 
class Bookingroom extends Component
{
    use WithPagination;

    // UI state
    public string $search = '';
    public ?int $departmentFilterId = null; 
    public ?int $roomFilterId = null; 
    public int $perPage = 10;
    public string $sortBy = 'start_time';
    public string $sortDir = 'desc';
    public bool $withTrashed = false;
    
    // Filters for new design
    public ?string $selectedDate = null; 
    public string $dateMode = 'semua'; 

    // For Receptionist look and feel
    public string $activeTab = 'all'; // all | done | rejected
    public string $typeScope = 'all'; // all | offline | online
    
    // Mobile UI state
    public bool $showFilterModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentFilterId' => ['except' => null],
        'roomFilterId' => ['except' => null],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'start_time'],
        'sortDir' => ['except' => 'desc'],
        'withTrashed' => ['except' => false],
        'selectedDate' => ['except' => null],
        'dateMode' => ['except' => 'semua'],
        'typeScope' => ['except' => 'all'],
        'activeTab' => ['except' => 'all'],
    ];

    // Modal & edit state
    public bool $modal = false;
    public ?int $editingId = null;

    // Form fields (same as original)
    public $room_id;
    public $department_id;
    public $meeting_title;
    public $date;
    public $number_of_attendees;
    public $start_time;
    public $end_time;
    public $special_notes;

    // Requirement checklist in modal
    public array $selectedRequirements = [];
    public $allRequirements = []; 

    // Lookups for list cards and filters
    public array $roomLookup = [];
    public array $roomsOptions = []; 
    public array $deptLookup = [];
    public array $deptOptions = []; 

    private array $sortable = ['meeting_title', 'date', 'start_time', 'end_time', 'number_of_attendees'];

    protected function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', Rule::exists('rooms', 'room_id')],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'department_id')],
            'meeting_title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'number_of_attendees' => ['required', 'integer', 'min:1'],
            'start_time' => ['required', 'date'], 
            'end_time' => ['required', 'date', 'after:start_time'],
            'special_notes' => ['nullable', 'string'],
            'selectedRequirements' => ['array'],
        ];
    }

    public function mount(): void
    {
        $companyId = Auth::user()->company_id;

        // Display column for Room
        $roomNameCol = Schema::hasColumn('rooms', 'name') ? 'name'
            : (Schema::hasColumn('rooms', 'room_name') ? 'room_name'
                : (Schema::hasColumn('rooms', 'room_number') ? 'room_number' : null));
        if (!$roomNameCol) {
            throw new \RuntimeException('Rooms table needs a display column (name / room_name / room_number).');
        }

        $rooms = RM::where('company_id', $companyId)
            ->orderBy($roomNameCol)
            ->get(['room_id', "$roomNameCol as name"]);
            
        $this->roomLookup = $rooms->pluck('name', 'room_id')->toArray();
        $this->roomsOptions = $rooms->map(fn($r) => [
            'id' => $r->room_id, 
            'label' => $r->name
        ])->values()->all();

        // Display column for Department
        $deptNameCol = Schema::hasColumn('departments', 'name') ? 'name'
            : (Schema::hasColumn('departments', 'department_name') ? 'department_name' : null);
        if (!$deptNameCol) {
            throw new \RuntimeException('Departments table needs a display column (name / department_name).');
        }

        $departments = DP::where('company_id', $companyId)
            ->orderBy($deptNameCol)
            ->get(['department_id', "$deptNameCol as name"]);
        
        $this->deptLookup = $departments->pluck('name', 'department_id')->toArray();
        $this->deptOptions = $departments->map(fn($d) => [
            'id' => $d->department_id, 
            'label' => $d->name
        ])->values()->all();

        // Load requirement master (scoped to company_id)
        $this->allRequirements = Requirement::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['requirement_id', 'name']);

        if (!in_array($this->sortBy, $this->sortable, true))
            $this->sortBy = 'start_time';
        $this->sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';
    }

    // Pagination refreshers for all filters
    public function updatingSearch() {$this->resetPage();}
    public function updatingDepartmentFilterId() {$this->resetPage();}
    public function updatingRoomFilterId() {$this->resetPage();} 
    public function updatingPerPage() {$this->resetPage();}
    public function updatingWithTrashed() {$this->resetPage();}
    public function updatingSelectedDate() {$this->resetPage();}
    public function updatingDateMode() {$this->resetPage();}
    public function updatingTypeScope() {$this->resetPage();}
    public function updatingActiveTab() {$this->resetPage();}

    public function sort(string $field): void
    {
        if (!in_array($field, $this->sortable, true))
            return;
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    /* ---------- Filters & Tabs ---------- */
    public function setTab(string $tab): void
    {
        if (in_array($tab, ['all', 'done', 'rejected'], true)) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function setTypeScope(string $scope): void
    {
        if (!in_array($scope, ['all', 'offline', 'online'], true)) {
            return;
        }

        $this->typeScope = $scope;
        $this->resetPage();
    }

    public function selectDepartment(int $deptId): void
    {
        $this->departmentFilterId = $deptId;
        $this->resetPage();
        $this->closeFilterModal();
    }

    public function clearDepartmentFilter(): void
    {
        $this->departmentFilterId = null;
        $this->resetPage();
        $this->closeFilterModal();
    }
    
    public function selectRoom(int $roomId): void 
    {
        $this->roomFilterId = $roomId;
        $this->resetPage();
        $this->closeFilterModal();
    }

    public function clearRoomFilter(): void 
    {
        $this->roomFilterId = null;
        $this->resetPage();
        $this->closeFilterModal();
    }
    
    /* ---------- Mobile Modal Methods ---------- */
    public function openFilterModal(): void
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal(): void
    {
        $this->showFilterModal = false;
    }

    /* ---------- Edit-only Modal ---------- */
    // Note: This method must be public to be called by Livewire.
    public function openEdit(int $id): void
    {
        $companyId = Auth::user()->company_id;
        $data = BR::where('company_id', $companyId)->withTrashed()->findOrFail($id); 

        $this->editingId = $data->bookingroom_id;

        $this->room_id = $data->room_id;
        $this->department_id = $data->department_id;
        $this->meeting_title = $data->meeting_title;
        $this->date = $data->date;
        $this->number_of_attendees = $data->number_of_attendees;
        $this->start_time = Carbon::parse($data->start_time)->format('Y-m-d\TH:i'); 
        $this->end_time = Carbon::parse($data->end_time)->format('Y-m-d\TH:i');
        $this->special_notes = $data->special_notes;

        // Load selected requirements (pivot)
        $this->selectedRequirements = DB::table('booking_requirements')
            ->where('bookingroom_id', $id)
            ->pluck('requirement_id')
            ->toArray();

        $this->modal = true;
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->modal = false;
        $this->resetErrorBag();
        $this->resetForm();
    }

    public function update(): void
    {
        $this->validate();

        $companyId = Auth::user()->company_id;
        $row = BR::where('company_id', $companyId)->withTrashed()->findOrFail($this->editingId); 

        // Update booking data
        $row->update([
            'room_id' => $this->room_id,
            'department_id' => $this->department_id,
            'meeting_title' => $this->meeting_title,
            'date' => $this->date,
            'number_of_attendees' => $this->number_of_attendees,
            'start_time' => Carbon::parse($this->start_time)->toDateTimeString(), 
            'end_time' => Carbon::parse($this->end_time)->toDateTimeString(),
            'special_notes' => $this->special_notes,
        ]);

        // Sync requirements (pivot)
        DB::table('booking_requirements')
            ->where('bookingroom_id', $row->bookingroom_id)
            ->delete();

        $now = now();
        if(!empty($this->selectedRequirements)){
            $inserts = [];
            foreach ($this->selectedRequirements as $reqId) {
                $inserts[] = [
                    'bookingroom_id' => $row->bookingroom_id,
                    'requirement_id' => $reqId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('booking_requirements')->insert($inserts);
        }

        $this->closeModal();
        session()->flash('success', 'Booking updated (requirements included).');
    }

    public function delete(int $id): void
    {
        $companyId = Auth::user()->company_id;
        $row = BR::where('company_id', $companyId)->findOrFail($id);
        $row->delete(); 
        session()->flash('success', 'Booking moved to trash (soft deleted).');
        $this->resetPage();
    }

    public function restore(int $id): void
    {
        $companyId = Auth::user()->company_id;
        $row = BR::where('company_id', $companyId)->onlyTrashed()->findOrFail($id);
        $row->restore(); 
        session()->flash('success', 'Booking restored from trash.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'room_id', 'department_id', 'meeting_title', 'date', 
            'number_of_attendees', 'start_time', 'end_time', 'special_notes', 
            'selectedRequirements',
        ]);
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        // Base query
        $bookings = BR::query()
            ->select([
                'booking_rooms.*',
                DB::raw("COALESCE(u.full_name, '') as user_full_name"),
            ])
            ->leftJoin('users as u', 'u.user_id', '=', 'booking_rooms.user_id')
            ->where('booking_rooms.company_id', $companyId)
            
            // Apply trash scope
            ->when($this->withTrashed, fn($q) => $q->onlyTrashed())
            ->when(!$this->withTrashed, fn($q) => $q->whereNull('booking_rooms.deleted_at'))

            // FIXED: Apply active tab filter based on status
            ->when($this->activeTab === 'rejected', fn($q) => $q->where('booking_rooms.status', 'rejected'))
            ->when($this->activeTab === 'done', fn($q) => $q->where('booking_rooms.status', 'completed'))
            
            // Apply department filter
            ->when($this->departmentFilterId, fn($q) => $q->where('booking_rooms.department_id', $this->departmentFilterId))
            
            // ADDED: Apply room filter (only targets room bookings/offline type)
            ->when($this->roomFilterId, function ($q) {
                $q->where('booking_rooms.room_id', $this->roomFilterId)
                  ->where(function ($qq) {
                      // Logic to ensure it's an 'offline' type booking
                      $qq->whereNull('booking_type')
                         ->orWhereIn('booking_type', ['bookingroom', 'meeting'])
                         ->orWhereNotIn('booking_type', ['online_meeting', 'onlinemeeting']); 
                  });
            })
            
            // Apply type scope filter: online / offline / all
            ->when($this->typeScope === 'online', function ($q) {
                $q->whereIn('booking_type', ['online_meeting', 'onlinemeeting']);
            })
            ->when($this->typeScope === 'offline', function ($q) {
                $q->where(function ($qq) {
                    $qq->whereNull('booking_type')
                       ->orWhereIn('booking_type', ['bookingroom', 'meeting'])
                       ->orWhereNotIn('booking_type', ['online_meeting', 'onlinemeeting']);
                });
            })

            // Apply search filter
            ->when($this->search !== '', function ($q) {
                $s = "%{$this->search}%";
                $q->where(function ($qq) use ($s) {
                    $qq->where('booking_rooms.meeting_title', 'like', $s)
                        ->orWhere('booking_rooms.special_notes', 'like', $s);
                });
            })

            // Apply date filter
            ->when($this->selectedDate, fn ($q) => $q->whereDate('booking_rooms.date', $this->selectedDate))

            // Apply sort mode
            ->when($this->dateMode === 'terbaru', fn ($q) => $q->orderByDesc('booking_rooms.date')->orderByDesc('booking_rooms.start_time'))
            ->when($this->dateMode === 'terlama', fn ($q) => $q->orderBy('booking_rooms.date')->orderBy('booking_rooms.start_time'))
            ->when($this->dateMode === 'semua', fn ($q) => $q->orderBy($this->sortBy, $this->sortDir))

            ->paginate($this->perPage);

        // Build requirements map for the page
        $ids = $bookings->pluck('bookingroom_id')->all();

        $requirementsMap = [];
        if (!empty($ids)) {
            $rows = DB::table('booking_requirements as br')
                ->join('requirements as r', 'r.requirement_id', '=', 'br.requirement_id')
                ->whereIn('br.bookingroom_id', $ids)
                ->orderBy('r.name')
                ->get(['br.bookingroom_id', 'r.name']);

            foreach ($rows as $row) {
                $requirementsMap[$row->bookingroom_id][] = $row->name;
            }
        }

        return view('livewire.pages.superadmin.bookingroom', [
            'bookings' => $bookings,
            'roomLookup' => $this->roomLookup,
            'roomsOptions' => $this->roomsOptions, 
            'deptLookup' => $this->deptLookup,
            'deptOptions' => $this->deptOptions, 
            'requirementsMap' => $requirementsMap, 
            'allRequirements' => $this->allRequirements, 
        ]);
    }
}