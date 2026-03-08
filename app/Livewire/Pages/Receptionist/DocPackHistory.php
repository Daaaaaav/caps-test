<?php

namespace App\Livewire\Pages\Receptionist;

use App\Models\Delivery;
use App\Models\Department;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.receptionist')]
#[Title('Documents & Packages — History')]
class DocPackHistory extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filters
    public string $q = '';
    public ?string $selectedDate = null;
    public string $dateMode = 'semua';
    public string $type = 'all';
    public ?int $departmentId = null;
    public ?int $userId = null;
    public string $departmentQ = '';
    public string $userQ = '';

    // Pagination
    public int $perDone = 6;

    // Mobile filter modal
    public bool $showFilterModal = false;

    // Edit & Delete (soft)
    public bool $showEdit = false;
    public ?int $editId = null;
    public array $edit = [
        'item_name' => null,
        'nama_pengirim' => null,
        'nama_penerima' => null,
    ];

    protected $rules = [
        'edit.item_name' => 'nullable|string|max:255',
        'edit.nama_pengirim' => 'nullable|string|max:255',
        'edit.nama_penerima' => 'nullable|string|max:255',
    ];

    public function updated($name): void
    {
        if ($name === 'departmentId') {
            $this->userId = null;
        }

        if (in_array($name, ['q', 'selectedDate', 'dateMode', 'type', 'departmentId', 'userId', 'departmentQ', 'userQ'], true)) {
            $this->resetPage('donePage');
        }
    }

    // ───────── Mobile Filter Modal ─────────
    public function openFilterModal(): void
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal(): void
    {
        $this->showFilterModal = false;
    }

    private function base()
    {
        return Delivery::query()->byCompany(Auth::user()->company_id ?? null);
    }

    private function applySharedFilters($q)
    {
        if ($this->type !== 'all') {
            $q->where('type', $this->type);
        }

        if ($this->selectedDate) {
            $q->whereDate('created_at', $this->selectedDate);
        }

        if ($this->departmentId && Schema::hasColumn('deliveries', 'department_id')) {
            $q->where('department_id', $this->departmentId);
        }

        if (trim($this->departmentQ) !== '' && Schema::hasColumn('deliveries', 'department_id')) {
            $deptIds = Department::query()
                ->where('company_id', Auth::user()->company_id ?? null)
                ->whereNull('deleted_at')
                ->where('department_name', 'like', '%' . trim($this->departmentQ) . '%')
                ->pluck('department_id');
            if ($deptIds->isNotEmpty()) {
                $q->whereIn('department_id', $deptIds);
            } else {
                $q->whereRaw('0=1');
            }
        }

        if ($this->userId && Schema::hasColumn('deliveries', 'receptionist_id')) {
            $q->where('receptionist_id', $this->userId);
        }

        if (trim($this->userQ) !== '' && Schema::hasColumn('deliveries', 'receptionist_id')) {
            $userIds = UserModel::query()
                ->where('company_id', Auth::user()->company_id ?? null)
                ->whereNull('deleted_at')
                ->when($this->departmentId, fn($qq) => $qq->where('department_id', $this->departmentId))
                ->where('full_name', 'like', '%' . trim($this->userQ) . '%')
                ->pluck('user_id');
            if ($userIds->isNotEmpty()) {
                $q->whereIn('receptionist_id', $userIds);
            } else {
                $q->whereRaw('0=1');
            }
        }

        if (trim($this->q) !== '') {
            $term = '%' . trim($this->q) . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('item_name', 'like', $term)
                    ->orWhere('nama_pengirim', 'like', $term)
                    ->orWhere('nama_penerima', 'like', $term)
                    ->orWhere(function ($qqq) use ($term) {
                        $qqq->whereHas('receptionist', function ($u) use ($term) {
                            $u->where('full_name', 'like', $term);
                        });
                    });
            });
        }

        if ($this->dateMode === 'terbaru') {
            $q->latest('created_at');
        } elseif ($this->dateMode === 'terlama') {
            $q->oldest('created_at');
        }

        return $q;
    }

    public function getDoneProperty()
    {
        $q = $this->base()->whereIn('status', ['delivered', 'taken']);

        $this->applySharedFilters($q);

        $q->orderByRaw("
            COALESCE(
              CASE
                WHEN status = 'delivered' THEN UNIX_TIMESTAMP(pengiriman)
                WHEN status = 'taken'     THEN UNIX_TIMESTAMP(pengambilan)
                ELSE UNIX_TIMESTAMP(created_at)
              END, 0
            ) DESC
        ");

        return $q->with('receptionist')
            ->paginate($this->perDone, pageName: 'donePage');
    }

    public function openEdit(int $id): void
    {
        $row = $this->base()->findOrFail($id);
        $this->editId = $row->delivery_id ?? $row->id ?? $id;
        $this->edit = [
            'item_name' => $row->item_name,
            'nama_pengirim' => $row->nama_pengirim,
            'nama_penerima' => $row->nama_penerima,
        ];
        $this->showEdit = true;
    }

    public function saveEdit(): void
    {
        if (!$this->editId) {
            return;
        }

        $this->validate();

        $row = $this->base()->findOrFail($this->editId);
        $row->fill([
            'item_name' => $this->edit['item_name'],
            'nama_pengirim' => $this->edit['nama_pengirim'],
            'nama_penerima' => $this->edit['nama_penerima'],
        ]);
        $row->save();

        $this->showEdit = false;
        $this->editId = null;
        $this->resetPage('donePage');
        $this->dispatch('toast', type: 'success', title: 'Saved', message: 'Information successfully saved.', duration: 3000);
    }

    public function softDelete(int $id): void
    {
        $row = $this->base()->findOrFail($id);
        $row->delete();
        $this->resetPage('donePage');
        $this->dispatch('toast', type: 'success', title: 'Deleted', message: 'Information successfully deleted.', duration: 3000);
    }

    public function render()
    {
        $companyId = Auth::user()->company_id ?? null;

        $departments = Department::query()
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->when(trim($this->departmentQ) !== '', function ($q) {
                $q->where('department_name', 'like', '%' . trim($this->departmentQ) . '%');
            })
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);

        $users = UserModel::query()
            ->where('company_id', $companyId)
            ->when($this->departmentId, fn($q) => $q->where('department_id', $this->departmentId))
            ->when(trim($this->userQ) !== '', function ($q) {
                $q->where('full_name', 'like', '%' . trim($this->userQ) . '%');
            })
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get(['user_id', 'full_name']);

        return view('livewire.pages.receptionist.docpackhistory', [
            'done' => $this->done,
            'departments' => $departments,
            'users' => $users,
        ]);
    }
}