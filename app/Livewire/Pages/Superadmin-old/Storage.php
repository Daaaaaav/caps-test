<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Storage as StorageModel;

#[Layout('layouts.superadmin')]
#[Title('Storages')]
class Storage extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Derived from Auth
    public int $company_id = 0;

    // Filters
    public string $search = '';
    public bool $showTrashed = false; // toggle if you want to show trash (UI optional)

    // Create form
    public string $code = '';
    public string $name = '';
    public bool $is_active = true;

    // Edit modal state
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public string $edit_code = '';
    public string $edit_name = '';
    public bool $edit_is_active = true;

    public function mount(): void
    {
        $this->company_id = (int) (Auth::user()->company_id ?? 0);
    }

    /** Reset page when searching so results show from first page */
    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'storagesPage');
    }

    protected function createRules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('storages', 'code')
                    ->where(fn($q) => $q->where('company_id', $this->company_id))
                    ->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('storages', 'name')
                    ->where(fn($q) => $q->where('company_id', $this->company_id))
                    ->whereNull('deleted_at'),
            ],
            'is_active' => ['boolean'],
        ];
    }

    protected function editRules(): array
    {
        return [
            'edit_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('storages', 'code')
                    ->ignore($this->edit_id, 'storage_id')
                    ->where(fn($q) => $q->where('company_id', $this->company_id))
                    ->whereNull('deleted_at'),
            ],
            'edit_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('storages', 'name')
                    ->ignore($this->edit_id, 'storage_id')
                    ->where(fn($q) => $q->where('company_id', $this->company_id))
                    ->whereNull('deleted_at'),
            ],
            'edit_is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->validate($this->createRules());

        StorageModel::create([
            'company_id' => $this->company_id,
            'code' => trim($this->code),
            'name' => trim($this->name),
            'is_active' => (bool) $this->is_active,
        ]);

        $this->reset(['code', 'name', 'is_active']);
        $this->is_active = true;

        session()->flash('ok', 'Storage created.');
        $this->resetPage(pageName: 'storagesPage');
    }

    public function openEdit(int $id): void
    {
        $row = StorageModel::withTrashed()
            ->where('company_id', $this->company_id)
            ->whereKey($id)
            ->firstOrFail();

        $this->edit_id = $row->storage_id;
        $this->edit_code = (string) $row->code;
        $this->edit_name = (string) $row->name;
        $this->edit_is_active = (bool) $row->is_active;

        $this->modalEdit = true;
    }

    public function update(): void
    {
        if (is_null($this->edit_id))
            return;

        $this->validate($this->editRules());

        StorageModel::where('company_id', $this->company_id)
            ->where('storage_id', $this->edit_id)
            ->update([
                'code' => trim($this->edit_code),
                'name' => trim($this->edit_name),
                'is_active' => (bool) $this->edit_is_active,
            ]);

        $this->modalEdit = false;
        session()->flash('ok', 'Storage updated.');
    }

    /** Soft delete */
    public function delete(int $id): void
    {
        $row = StorageModel::where('company_id', $this->company_id)->findOrFail($id);
        $row->delete(); // soft delete
        session()->flash('ok', 'Storage moved to trash.');
        $this->resetPage(pageName: 'storagesPage');
    }

    /** Restore from soft delete */
    public function restore(int $id): void
    {
        $row = StorageModel::withTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        if ($row->trashed()) {
            $row->restore();
            session()->flash('ok', 'Storage restored.');
        }
    }

    /** Optional: permanently delete */
    public function forceDelete(int $id): void
    {
        $row = StorageModel::withTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        $row->forceDelete();
        session()->flash('ok', 'Storage permanently deleted.');
        $this->resetPage(pageName: 'storagesPage');
    }

    public function render()
    {
        $query = StorageModel::query()
            ->where('company_id', $this->company_id);

        // hide trashed by default (toggleable if you want)
        if (!$this->showTrashed) {
            $query->whereNull('deleted_at');
        }

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            // OR search on code or name
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%");
            });
        }

        $rows = $query->orderByDesc('created_at')->paginate(10, pageName: 'storagesPage');

        return view('livewire.pages.superadmin.storage', [
            'rows' => $rows,
        ]);
    }
}
