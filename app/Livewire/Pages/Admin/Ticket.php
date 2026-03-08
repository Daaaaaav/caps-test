<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket as TicketModel;
use App\Models\Department;
use App\Models\User; // Import User model for role checking
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Admin - Ticket')]
class Ticket extends Component
{
    use WithPagination;

    public ?string $search   = null;
    public ?string $priority = null;  // low|medium|high
    public ?string $status   = null;  // open|in_progress|resolved|closed
    public ?string $assignment = null; // unassigned|assigned

    protected string $paginationTheme = 'tailwind';

    private const ADMIN_ROLE_NAMES = ['Superadmin', 'Admin'];

    private const UI_TO_DB_STATUS_MAP = [
        'open'        => 'OPEN',
        'in_progress' => 'IN_PROGRESS',
        'resolved'    => 'RESOLVED',
        'closed'      => 'CLOSED',
    ];

    // ===== Department switcher =====
    public string $company_name = '-';
    public string $department_name = '-';
    public array  $deptOptions = [];              // [['id'=>..,'name'=>..], ...]
    public ?int   $selected_department_id = null; // live switch
    public ?int   $primary_department_id  = null; // users.department_id
    public bool $showSwitcher = false;
    
    // **[NEW PROPERTY]** To store the superadmin status
    public bool $is_superadmin_user = false; 

    public function mount(): void
    {
        $user = Auth::user()->loadMissing(['company', 'department', 'role']);
        
        // **[CHANGE]** Set the flag based on the loaded user role
        $this->is_superadmin_user = $this->isSuperadmin();

        $this->company_name        = optional($user->company)->company_name ?? '-';
        $this->primary_department_id = $user->department_id ?: null;

        $this->loadUserDepartments();

        // **[CHANGE]** Handle default selection for Superadmin
        if ($this->is_superadmin_user) {
            // Default to 'View All' (null ID)
            $this->selected_department_id = null;
            $this->department_name = 'SEMUA DEPARTEMEN';
        } else {
             // default selection for regular admin: primary or first option
            if (!$this->selected_department_id) {
                $this->selected_department_id = $this->primary_department_id
                    ?: ($this->deptOptions[0]['id'] ?? null);
            }
            $this->department_name = $this->resolveDeptName($this->selected_department_id);
        }
    }

    protected function loadUserDepartments(): void
    {
        $user = Auth::user();

        // **[CHANGE]** If Superadmin, load ALL departments for the company
        if ($this->is_superadmin_user) {
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


        $this->deptOptions = $rows
            ->map(fn($r) => ['id' => (int) $r->id, 'name' => (string) $r->name])
            ->values()
            ->all();

        // Add the primary department if not already included
        $primaryId = $user->department_id;
        $isPrimaryInList = collect($this->deptOptions)->contains('id', $primaryId);

        if ($primaryId && !$isPrimaryInList) {
             $primaryName = Department::where('department_id', $primaryId)->value('department_name') ?? 'Unknown';
             array_unshift($this->deptOptions, ['id' => (int)$primaryId, 'name' => (string)$primaryName]);
        }
        
        // **[CHANGE]** Add "View All" option for Superadmins
        if ($this->is_superadmin_user) {
            array_unshift($this->deptOptions, ['id' => null, 'name' => 'SEMUA DEPARTEMEN']);
        }

        $this->showSwitcher = count($this->deptOptions) > 1;

        // fallback: if no pivot but has primary (Only runs for non-superadmins)
        if (!$this->is_superadmin_user && empty($this->deptOptions) && $this->primary_department_id) {
            $name = Department::where('department_id', $this->primary_department_id)->value('department_name') ?? 'Unknown';
            $this->deptOptions = [
                [
                    'id'   => (int) $this->primary_department_id,
                    'name' => (string) $name,
                ],
            ];
            $this->showSwitcher = false;
        }
    }

    protected function resolveDeptName(?int $deptId): string
    {
        if (!$deptId) {
            return 'SEMUA DEPARTEMEN'; // Updated for Superadmin "View All"
        }

        foreach ($this->deptOptions as $opt) {
            if ($opt['id'] === (int) $deptId) {
                return $opt['name'];
            }
        }

        return Department::where('department_id', $deptId)->value('department_name') ?? '-';
    }

    public function resetToPrimaryDepartment(): void
    {
        if ($this->primary_department_id) {
            $this->selected_department_id = $this->primary_department_id;
            $this->department_name        = $this->resolveDeptName($this->selected_department_id);
            $this->resetPage();
        }
    }

    // Livewire may call either depending on version
    public function updatedSelectedDepartment_id(): void
    {
        $this->updatedSelectedDepartmentId();
    }

    public function updatedSelectedDepartmentId(): void
    {
        $id = $this->selected_department_id; // Keep as null if "View All" is selected

        if (!$this->is_superadmin_user) {
            $allowed = collect($this->deptOptions)->pluck('id')->all();
            $id = (int) $id;

            if (!in_array($id, $allowed, true)) {
                $this->selected_department_id = $this->primary_department_id
                    ?: ($this->deptOptions[0]['id'] ?? null);
                $id = $this->selected_department_id;
            }
        }
        
        $this->department_name = $this->resolveDeptName($id);
        $this->resetPage();
    }

    public function tick(): void
    {
        // used for wire:poll
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingAssignment(): void
    {
        $this->resetPage();
    }

    protected function currentAdmin()
    {
        $user = Auth::user();
        if ($user && !$user->relationLoaded('role')) {
            $user->load('role');
        }

        return $user;
    }

    protected function ensureAdmin(): bool
    {
        $u = $this->currentAdmin();

        return $u && $u->role && \in_array($u->role->name, self::ADMIN_ROLE_NAMES, true);
    }

    protected function isSuperadmin(): bool
    {
        $u = $this->currentAdmin();

        return $u && $u->role && $u->role->name === 'Superadmin';
    }

    public function resetFilters(): void
    {
        $this->search = $this->priority = $this->status = $this->assignment = null;
        $this->resetPage();
    }

    public function deleteTicket(int $ticketId): void
    {
        if (!$this->ensureAdmin()) {
            session()->flash('error', 'Unauthorized.');
            return;
        }

        $ticket = TicketModel::where('ticket_id', $ticketId)->first();
        if (!$ticket) {
            session()->flash('error', 'Ticket not found.');
            return;
        }

        $ticket->delete();
        $this->resetPage();
        session()->flash('message', "Ticket #{$ticketId} moved to Trash.");
    }

    public function restoreTicket(int $ticketId): void
    {
        if (!$this->ensureAdmin()) {
            session()->flash('error', 'Unauthorized.');
            return;
        }

        $ticket = TicketModel::onlyTrashed()->where('ticket_id', $ticketId)->first();
        if (!$ticket) {
            session()->flash('error', 'Trashed ticket not found.');
            return;
        }

        $ticket->restore();
        session()->flash('message', "Ticket #{$ticketId} restored.");
    }

    public function forceDeleteTicket(int $ticketId): void
    {
        if (!$this->isSuperadmin()) {
            session()->flash('error', 'Only Superadmin can permanently delete.');
            return;
        }

        $ticket = TicketModel::onlyTrashed()->where('ticket_id', $ticketId)->first();
        if (!$ticket) {
            session()->flash('error', 'Trashed ticket not found.');
            return;
        }

        $ticket->forceDelete();
        session()->flash('message', "Ticket #{$ticketId} permanently deleted.");
    }

    public function render()
    {
        $query = TicketModel::query()
            ->with([
                'user:user_id,full_name,department_id',
                'department:department_id,department_name',
                'assignment.agent:user_id,full_name',
                'attachments:attachment_id,ticket_id,file_url,file_type,original_filename,bytes',
            ])
            ->withCount('attachments')
            ->orderByDesc('ticket_id');

        // Get the authenticated user
        $user = auth()->user();

        // Apply company filter (mandatory)
        if (Schema::hasColumn('tickets', 'company_id') && isset($user->company_id)) {
            $query->where('company_id', $user->company_id);
        }

        // Determine the department filter ID. Null means 'View All'.
        $deptId = $this->selected_department_id; 

        // **[CHANGE]** Department Filtering Logic
        if (Schema::hasColumn('tickets', 'department_id')) {
            if ($deptId !== null) {
                // Filter by the specific department selected in the dropdown
                $query->where('department_id', $deptId);
            } elseif (!$this->is_superadmin_user) {
                // If 'View All' is selected (null) AND the user is NOT Superadmin, 
                // fall back to filtering by the user's primary department (standard admin behavior)
                $query->where('department_id', $user->department_id);
            }
            // If $deptId is null AND the user IS Superadmin, no department filter is applied (shows all).
        }

        // The original block for Admin role (non-Superadmin) is no longer needed here 
        // because the logic above handles both department selection and the default view correctly:
        // - Superadmin + null $deptId -> No filter (view all)
        // - Admin + specific $deptId -> Filter by $deptId
        // - Admin + null $deptId (fallback) -> Filter by $user->department_id

        // Search functionality
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(fn($q) => $q
                ->where('subject', 'like', $s)
                ->orWhere('description', 'like', $s));
        }

        // Filter by priority
        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        // Filter by status
        if ($this->status) {
            $dbStatus = self::UI_TO_DB_STATUS_MAP[$this->status] ?? null;
            if ($dbStatus) {
                $query->where('status', $dbStatus);
            }
        }

        // Filter by assignment
        if ($this->assignment) {
            if ($this->assignment === 'unassigned') {
                $query->whereDoesntHave('assignment');
            } elseif ($this->assignment === 'assigned') {
                $query->whereHas('assignment');
            }
        }

        // Paginate the results
        $tickets = $query->paginate(12);

        return view('livewire.pages.admin.ticket', compact('tickets'));
    }
}