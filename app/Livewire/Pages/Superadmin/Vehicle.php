<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Vehicle as VehicleModel;

#[Layout('layouts.superadmin')]
#[Title('Management Vehicle')]
class Vehicle extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'tailwind';

    public int $company_id = 0;

    // Filters
    public string $search = '';
    public bool $showTrashed = false;

    // Create form
    public string $name = '';
    public string $category = '';
    public string $plate_number = '';
    public string $year = '';
    public bool $is_active = true;
    public string $notes = '';

    // Edit modal
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public string $edit_name = '';
    public string $edit_category = '';
    public string $edit_plate_number = '';
    public string $edit_year = '';
    public bool $edit_is_active = true;
    public string $edit_notes = '';

    public function mount(): void
    {
        $this->company_id = (int) (Auth::user()->company_id ?? 0);
    }

    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'vehiclesPage');
    }

    protected function createRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('vehicles', 'name')
                    ->where(fn($q) => $q->where('company_id', $this->company_id))
                    ->whereNull('deleted_at'),
            ],
            'category' => 'required|string|max:100',
            'plate_number' => 'required|string|max:50',
            'year' => 'required|string|max:10',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:255',
        ];
    }

    protected function editRules(): array
    {
        return [
            'edit_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('vehicles', 'name')
                    ->ignore($this->edit_id, 'vehicle_id')
                    ->where(fn($q) => $q->where('company_id', $this->company_id))
                    ->whereNull('deleted_at'),
            ],
            'edit_category' => 'required|string|max:100',
            'edit_plate_number' => 'required|string|max:50',
            'edit_year' => 'required|string|max:10',
            'edit_is_active' => 'boolean',
            'edit_notes' => 'nullable|string|max:255',
        ];
    }

    public function create(): void
    {
        $this->validate($this->createRules());

        VehicleModel::create([
            'company_id' => $this->company_id,
            'name' => trim($this->name),
            'category' => trim($this->category),
            'plate_number' => trim($this->plate_number),
            'year' => trim($this->year),
            'is_active' => (bool) $this->is_active,
            'notes' => trim($this->notes),
        ]);

        $this->reset(['name', 'category', 'plate_number', 'year', 'is_active', 'notes']);
        $this->is_active = true;

        session()->flash('ok', 'Vehicle created.');
        $this->resetPage(pageName: 'vehiclesPage');
    }

    public function openEdit(int $id): void
    {
        $row = VehicleModel::withTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        $this->edit_id = $row->vehicle_id;
        $this->edit_name = $row->name;
        $this->edit_category = $row->category;
        $this->edit_plate_number = $row->plate_number;
        $this->edit_year = $row->year;
        $this->edit_is_active = (bool) $row->is_active;
        $this->edit_notes = $row->notes ?? '';

        $this->modalEdit = true;
    }

    public function update(): void
    {
        if (!$this->edit_id)
            return;
        $this->validate($this->editRules());

        VehicleModel::where('company_id', $this->company_id)
            ->where('vehicle_id', $this->edit_id)
            ->update([
                'name' => trim($this->edit_name),
                'category' => trim($this->edit_category),
                'plate_number' => trim($this->edit_plate_number),
                'year' => trim($this->edit_year),
                'is_active' => (bool) $this->edit_is_active,
                'notes' => trim($this->edit_notes),
            ]);

        $this->modalEdit = false;
        session()->flash('ok', 'Vehicle updated.');
    }

    public function delete(int $id): void
    {
        $row = VehicleModel::where('company_id', $this->company_id)->findOrFail($id);
        $row->delete();
        session()->flash('ok', 'Vehicle moved to trash.');
        $this->resetPage(pageName: 'vehiclesPage');
    }

    public function restore(int $id): void
    {
        $row = VehicleModel::withTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        if ($row->trashed()) {
            $row->restore();
            session()->flash('ok', 'Vehicle restored.');
        }
    }

    public function forceDelete(int $id): void
    {
        $row = VehicleModel::withTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        $row->forceDelete();
        session()->flash('ok', 'Vehicle permanently deleted.');
        $this->resetPage(pageName: 'vehiclesPage');
    }

    public function render()
    {
        $query = VehicleModel::query()->where('company_id', $this->company_id);

        if (!$this->showTrashed)
            $query->whereNull('deleted_at');

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('plate_number', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%");
            });
        }

        $rows = $query->orderByDesc('created_at')->paginate(10, pageName: 'vehiclesPage');

        return view('livewire.pages.superadmin.vehicle', ['rows' => $rows]);
    }
}
