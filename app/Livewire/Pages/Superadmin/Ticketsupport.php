<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Ticket;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection; // Use for type hinting

#[Layout('layouts.superadmin')]
#[Title('Ticket Support')]
class Ticketsupport extends Component
{
    use WithPagination;

    // --- List Filters ---
    public $search = '';
    public $departmentFilter = '';
    public $priorityFilter = '';
    public $perPage = 10;
    public bool $showDeleted = false;
    public bool $showFilterModal = false;

    // --- Edit Modal Props ---
    public $modal = false;
    public $editingTicketId = null;
    public $subject, $description, $priority, $department_id, $status;

    // --- Detail Modal Props ---
    public bool $detailModal = false;
    // Store only the essential ticket data as an array to avoid Livewire serialization issues with complex relations
    public ?array $selectedTicketData = null; 

    public $deptLookup = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'perPage' => ['except' => 10],
        'showDeleted' => ['except' => false], 
    ];

    protected $rules = [
        'subject' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high,urgent',
        'department_id' => 'nullable|exists:departments,department_id',
        'status' => 'required|string|in:OPEN,IN_PROGRESS,RESOLVED,CLOSED',
    ];

    public function mount()
    {
        $companyId = Auth::user()->company_id;
        $deptNameCol = Schema::hasColumn('departments', 'name') ? 'name' : 'department_name';

        $this->deptLookup = Department::where('company_id', $companyId)
            ->pluck($deptNameCol, 'department_id')
            ->toArray();

        if (!$this->priority) {
            $this->priority = 'low';
        }
    }

    // --- Pagination / Filter Updates ---
    public function updatingSearch() { $this->resetPage(); }
    public function updatingDepartmentFilter() { $this->resetPage(); $this->closeFilterModal(); }
    public function updatingPriorityFilter() { $this->resetPage(); $this->closeFilterModal(); }
    public function updatingPerPage() { $this->resetPage(); }
    public function updatedShowDeleted() { $this->resetPage(); }

    // --- Detail Modal Actions ---

    public function openTicketDetails(int $id): void
    {
        $companyId = Auth::user()->company_id;
        
        // Load ticket with all required relations
        $ticket = Ticket::withTrashed()
            ->with([
                'user', 
                'department', 
                'attachments', 
                'requesterDepartment', 
                'comments.user', 
                'assignments.user' // Added assignments relation for agent info
            ])
            ->where('company_id', $companyId)
            ->findOrFail($id);
            
        // Convert the model and its relations to a safe array for Livewire state
        // This prevents Livewire from trying to synthesize complex collections (like comments)
        $this->selectedTicketData = $ticket->toArray();
        $this->selectedTicketData['user'] = $ticket->user->toArray();
        $this->selectedTicketData['department'] = $ticket->department?->toArray();
        $this->selectedTicketData['requester_department'] = $ticket->requesterDepartment?->toArray();
        $this->selectedTicketData['attachments'] = $ticket->attachments->toArray();
        $this->selectedTicketData['comments'] = $ticket->comments->map(function ($comment) {
            return array_merge($comment->toArray(), ['user' => $comment->user->toArray()]);
        })->toArray();
        $this->selectedTicketData['assignments'] = $ticket->assignments->map(function ($assignment) {
            return array_merge($assignment->toArray(), ['user' => $assignment->user->toArray()]);
        })->toArray();


        $this->detailModal = true;
    }

    public function closeTicketDetails(): void
    {
        $this->detailModal = false;
        $this->selectedTicketData = null;
    }
    
    // --- Mobile Modal Actions ---
    public function openFilterModal(): void
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal(): void
    {
        $this->showFilterModal = false;
    }
    
    // --- CRUD Actions ---
    public function openEdit($id)
    {
        $t = Ticket::withTrashed()->findOrFail($id);
        
        if ($t->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->editingTicketId = $t->ticket_id;
        $this->subject = $t->subject;
        $this->description = $t->description;
        $this->priority = $t->priority ?? 'low';
        $this->department_id = $t->department_id;
        $this->status = $t->status ?? 'OPEN';
        $this->modal = true;

        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->reset(['editingTicketId', 'subject', 'description', 'priority', 'department_id', 'status']);
        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        $t = Ticket::withTrashed()->findOrFail($this->editingTicketId);
        $t->update([
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'department_id' => $this->department_id,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Ticket updated successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $t = Ticket::findOrFail($id);
        $t->delete();
        session()->flash('success', 'Ticket moved to trash (soft deleted).');
        $this->resetPage();
    }

    public function restore($id)
    {
        $t = Ticket::onlyTrashed()->findOrFail($id);
        $t->restore();
        session()->flash('success', 'Ticket restored successfully.');
        $this->resetPage();
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;

        $query = Ticket::with(['user', 'attachments', 'department'])
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc');

        if ($this->showDeleted) {
            $query->onlyTrashed();
        }

        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('subject', 'like', $s)
                    ->orWhere('description', 'like', $s)
                    ->orWhereHas('user', function ($qu) use ($s) {
                        $qu->where('full_name', 'like', $s)
                            ->orWhere('email', 'like', $s);
                    });
            });
        }

        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        $tickets = $query->paginate($this->perPage);

        return view('livewire.pages.superadmin.ticketsupport', [
            'tickets' => $tickets,
            'deptLookup' => $this->deptLookup,
        ]);
    }
}