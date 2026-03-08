<?php
// app/Livewire/Pages/Superadmin/Managerequirement.php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Requirement;

#[Layout('layouts.superadmin')]
#[Title('Manage Requirement')]
class Managerequirement extends Component
{
    use WithPagination;

    // Scope berdasar company user login
    public int $companyId;

    // Create
    public string $req_name = '';
    public string $req_search = '';

    // Edit modal
    public bool $reqModal = false;
    public ?int $req_edit_id = null;
    public string $req_edit_name = '';

    // View controls
    public bool $showDeleted = false;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->companyId = (int) (Auth::user()->company_id ?? 0);
    }

    public function updatingReqSearch(): void
    {
        $this->resetPage(pageName: 'reqsPage');
    }

    public function updatedShowDeleted(): void
    {
        $this->resetPage(pageName: 'reqsPage');
    }

    protected function baseQuery(): Builder
    {
        $q = Requirement::query()
            ->where('company_id', $this->companyId);

        return $this->showDeleted ? $q->onlyTrashed() : $q;
    }

    protected function reqCreateRules(): array
    {
        return [
            'req_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('requirements', 'name')
                    ->where(fn($q) => $q
                        ->where('company_id', $this->companyId)
                        ->whereNull('deleted_at')),
            ],
        ];
    }

    protected function reqEditRules(): array
    {
        return [
            'req_edit_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('requirements', 'name')
                    ->ignore($this->req_edit_id, 'requirement_id')
                    ->where(fn($q) => $q
                        ->where('company_id', $this->companyId)
                        ->whereNull('deleted_at')),
            ],
        ];
    }

    // Create
    public function reqStore(): void
    {
        $this->validate($this->reqCreateRules());

        Requirement::create([
            'name' => $this->req_name,
            'company_id' => $this->companyId, // SELALU dari user login
        ]);

        $this->req_name = '';
        session()->flash('success', 'Requirement berhasil dibuat.');
        $this->resetPage(pageName: 'reqsPage');
    }

    // Edit open
    public function reqOpenEdit(int $id): void
    {
        $q = Requirement::withTrashed()
            ->where('company_id', $this->companyId)
            ->where('requirement_id', $id)
            ->firstOrFail();

        $this->req_edit_id = $q->requirement_id;
        $this->req_edit_name = $q->name;
        $this->reqModal = true;
        $this->resetErrorBag();
    }

    public function reqCloseEdit(): void
    {
        $this->reqModal = false;
        $this->reset('req_edit_id', 'req_edit_name');
        $this->resetErrorBag();
    }

    public function reqUpdate(): void
    {
        $this->validate($this->reqEditRules());

        $q = Requirement::withTrashed()
            ->where('company_id', $this->companyId)
            ->where('requirement_id', $this->req_edit_id)
            ->firstOrFail();

        $q->update(['name' => $this->req_edit_name]);

        $this->reqCloseEdit();
        session()->flash('success', 'Requirement berhasil diupdate.');
    }

    // Soft delete only
    public function reqDelete(int $id): void
    {
        $q = Requirement::where('company_id', $this->companyId)
            ->where('requirement_id', $id)
            ->firstOrFail();

        $q->delete(); // soft delete

        if ($this->req_edit_id === $id) {
            $this->reqCloseEdit();
        }

        session()->flash('success', 'Requirement berhasil dihapus (soft delete).');
        $this->resetPage(pageName: 'reqsPage');
    }

    // Restore
    public function reqRestore(int $id): void
    {
        $q = Requirement::onlyTrashed()
            ->where('company_id', $this->companyId)
            ->where('requirement_id', $id)
            ->firstOrFail();

        $q->restore();

        session()->flash('success', 'Requirement berhasil direstore.');
        $this->resetPage(pageName: 'reqsPage');
    }

    public function getRequirementsProperty()
    {
        return $this->baseQuery()
            ->when(
                $this->req_search !== '',
                fn($q) =>
                $q->where('name', 'like', "%{$this->req_search}%")
            )
            ->orderBy('name')
            ->paginate(10, pageName: 'reqsPage');
    }

    public function render()
    {
        return view('livewire.pages.superadmin.managerequirement', [
            'requirements' => $this->requirements,
        ]);
    }
}
