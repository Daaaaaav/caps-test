<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Information as InformationModel;
use App\Models\Department;

#[Layout('layouts.superadmin')]
#[Title('Information')]
class Information extends Component
{
    use WithPagination;

    // Derived from Auth
    public ?int $company_id = null;

    // Table filter & sorting
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public ?int $filter_department_id = null; // NEW: filter list by department

    // Create form fields
    public string $description = '';
    public ?string $event_at = null;
    public ?int $department_id = null; // NEW: assign department when creating

    // Edit modal state & fields
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public string $edit_description = '';
    public ?string $edit_event_at = null;
    public ?int $edit_department_id = null; // NEW

    /** Options */
    public array $departmentOptions = []; // for selects

    protected function rules(): array
    {
        return [
            'description'   => 'required|string|max:255',
            'event_at'      => 'nullable|date',
            'department_id' => 'required|integer|exists:departments,department_id',
        ];
    }

    protected function editRules(): array
    {
        return [
            'edit_description'   => 'required|string|max:255',
            'edit_event_at'      => 'nullable|date',
            'edit_department_id' => 'required|integer|exists:departments,department_id',
        ];
    }

    public function mount(): void
    {
        $this->company_id = Auth::user()?->company_id;

        // preload department options for this company
        $this->departmentOptions = Department::query()
            ->where('company_id', $this->company_id)
            ->orderBy('department_name')
            ->get(['department_id','department_name'])
            ->map(fn($d) => ['id' => $d->department_id, 'name' => $d->department_name])
            ->toArray();

        // default create form to first department (optional)
        if (!$this->department_id && !empty($this->departmentOptions)) {
            $this->department_id = $this->departmentOptions[0]['id'];
        }
    }

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFilterDepartmentId(): void { $this->resetPage(); }

    /**
     * Create new information.
     */
    public function store(): void
    {
        $this->validate();

        InformationModel::create([
            'company_id'    => $this->company_id,
            'department_id' => $this->department_id,
            'description'   => $this->description,
            'event_at'      => $this->event_at ? Carbon::parse($this->event_at) : null,
        ]);

        $this->reset('description', 'event_at', 'department_id');
        // set back default department to first option (optional)
        if (!empty($this->departmentOptions)) {
            $this->department_id = $this->departmentOptions[0]['id'];
        }

        $this->dispatch('toast', type: 'success', title: 'Dibuat', message: 'Information berhasil dibuat.', duration: 3000);
        $this->resetPage();
    }

    /**
     * Open and prepare the edit modal.
     */
    public function openEdit(int $id): void
    {
        $info = InformationModel::where('company_id', $this->company_id)->findOrFail($id);

        $this->edit_id            = $info->getKey();
        $this->edit_description   = $info->description;
        $this->edit_event_at      = $info->event_at ? $info->event_at->format('Y-m-d\TH:i') : null;
        $this->edit_department_id = $info->department_id;

        $this->modalEdit = true;
        $this->resetErrorBag();
    }

    public function closeEdit(): void
    {
        $this->modalEdit = false;
        $this->reset('edit_id', 'edit_description', 'edit_event_at', 'edit_department_id');
    }

    /**
     * Update the information from the edit modal.
     */
    public function update(): void
    {
        $this->validate($this->editRules());

        $info = InformationModel::where('company_id', $this->company_id)->findOrFail($this->edit_id);

        $info->update([
            'description'   => $this->edit_description,
            'event_at'      => $this->edit_event_at ? Carbon::parse($this->edit_event_at) : null,
            'department_id' => $this->edit_department_id,
        ]);

        $this->closeEdit();
        $this->dispatch('toast', type: 'success', title: 'Diupdate', message: 'Information berhasil diupdate.', duration: 3000);
        $this->resetPage();
    }

    /**
     * Soft delete an information entry.
     */
    public function delete(int $id): void
    {
        InformationModel::where('company_id', $this->company_id)
            ->findOrFail($id)
            ->delete(); // soft delete (model must use SoftDeletes)

        $this->resetPage();
        $this->dispatch('toast', type: 'success', title: 'Dihapus', message: 'Information berhasil dihapus (soft delete).', duration: 3000);
    }

    public function render()
    {
        $information = InformationModel::query()
            ->where('company_id', $this->company_id)
            ->when($this->filter_department_id, fn($q) => $q->where('department_id', $this->filter_department_id))
            ->when($this->search, function ($query) {
                $query->where('description', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.pages.superadmin.information', [
            'information'       => $information,
            'departmentOptions' => $this->departmentOptions,
        ]);
    }
}
