<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Throwable;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;

#[Layout('layouts.superadmin')]
#[Title('Admin Settings')]
class Adminmanagement extends Component
{
    use WithPagination;

    // ===== UI State =====
    public string $mode = 'index'; // index|create|edit
    public string $search = '';
    public int    $perPage = 10;
    public ?int   $editingId = null;
    public bool   $showEditModal = false;

    // ===== Scope =====
    public int    $company_id;
    public string $company_name = '-';

    // ===== Form fields =====
    public string  $full_name = '';
    public string  $email = '';
    public ?string $phone_number = null;
    public string  $password = '';
    public string  $password_confirmation = '';

    /** Primary department -> stored in users.department_id */
    public ?int $primary_department_id = null;

    /** Additional departments -> stored in pivot user_departments (excluding primary) */
    public array $additional_departments = [];

    // ===== Options =====
    /** @var array<int, array{department_id:int,department_name:string}> */
    public array $departments = [];

    // ===== Fixed role (Admin) =====
    protected int $adminRoleId = 0; // initialized

    /* ------------------------------------------------------------------------
     | Lifecycle
     * --------------------------------------------------------------------- */

    public function mount(): void
    {
        $auth = Auth::user()->loadMissing('company');
        $this->company_id   = (int) ($auth->company_id ?? 0);
        $this->company_name = optional($auth->company)->company_name ?? '-';

        $this->adminRoleId  = $this->resolveAdminRoleId();
        $this->refreshDepartments();
    }

    public function hydrate(): void
    {
        if (!$this->adminRoleId) {
            $this->adminRoleId = $this->resolveAdminRoleId();
        }
        if (!$this->company_id) {
            $auth = Auth::user()->loadMissing('company');
            $this->company_id   = (int) ($auth->company_id ?? 0);
            $this->company_name = optional($auth->company)->company_name ?? '-';
        }
    }

    /* ------------------------------------------------------------------------
     | Helpers
     * --------------------------------------------------------------------- */

    protected function resolveAdminRoleId(): int
    {
        $env = (int) env('ADMIN_ROLE_ID', 0);
        if ($env > 0 && Role::whereKey($env)->exists()) {
            return $env;
        }
        $role = Role::whereRaw('LOWER(name) = ?', ['admin'])->first();
        if ($role) return (int) $role->getKey();
        $id = Role::where('name', 'Admin')->value('role_id');
        return $id ? (int) $id : 2;
    }

    public function refreshDepartments(): void
    {
        $this->departments = Department::where('company_id', $this->company_id)
            ->orderBy('department_name')
            ->get(['department_id', 'department_name'])
            ->toArray();
    }

    /* ------------------------------------------------------------------------
     | CRUD
     * --------------------------------------------------------------------- */

    public function create(): void
    {
        $this->resetCreateForm();
        $this->mode = 'create';
    }

    public function edit(int $userId): void
    {
        $user = User::with(['departments', 'department'])
            ->where('company_id', $this->company_id)
            ->where('role_id', $this->adminRoleId)
            ->findOrFail($userId);

        // prefill inputs
        $this->editingId             = $user->user_id;
        $this->full_name             = (string) $user->full_name;
        $this->email                 = (string) $user->email;
        $this->phone_number          = $user->phone_number;
        $this->primary_department_id = $user->department_id ? (int) $user->department_id : null;

        // additional = all dept relations except primary
        $this->additional_departments = $user->departments()
            ->when($this->primary_department_id, fn($q) => $q->where('departments.department_id', '<>', $this->primary_department_id))
            ->pluck('departments.department_id')
            ->map(fn($v) => (int) $v)
            ->values()
            ->toArray();

        $this->password = '';
        $this->password_confirmation = '';

        $this->mode = 'edit';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function store(): void
    {
        $this->validate($this->rules());

        // Bersihkan additional: unik & bukan primary
        $additional = array_values(array_unique(array_filter(
            $this->additional_departments,
            fn($id) => (int) $id !== (int) $this->primary_department_id
        )));

        try {
            DB::transaction(function () use ($additional) {
                $user = User::create([
                    'company_id'    => $this->company_id,
                    'department_id' => $this->primary_department_id,
                    'role_id'       => $this->adminRoleId,
                    'full_name'     => $this->full_name,
                    'email'         => strtolower($this->email),
                    'phone_number'  => $this->phone_number,
                    'password'      => Hash::make($this->password),
                ]);

                // simpan semua additional ke pivot
                $user->departments()->sync($additional);
            });

            $this->dispatch('toast', type: 'success', title: 'Dibuat', message: 'Admin berhasil dibuat.', duration: 3000);
            $this->resetCreateForm();
            $this->mode = 'index';
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Create failed', message: $e->getMessage(), duration: 5000);
        }
    }

    public function update(): void
    {
        $this->validate($this->rules(editing: true));

        $additional = array_values(array_unique(array_filter(
            $this->additional_departments,
            fn($id) => (int) $id !== (int) $this->primary_department_id
        )));

        try {
            DB::transaction(function () use ($additional) {
                $user = User::where('company_id', $this->company_id)
                    ->where('role_id', $this->adminRoleId)
                    ->findOrFail($this->editingId);

                $payload = [
                    'department_id' => $this->primary_department_id,
                    'full_name'     => $this->full_name,
                    'email'         => strtolower($this->email),
                    'phone_number'  => $this->phone_number,
                ];

                if (strlen($this->password) > 0) {
                    $payload['password'] = Hash::make($this->password);
                }

                $user->update($payload);
                $user->departments()->sync($additional);
            });

            $this->dispatch('toast', type: 'success', title: 'Diupdate', message: 'Admin diupdate.', duration: 3000);
            $this->showEditModal = false;
            $this->mode = 'index';
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Update failed', message: $e->getMessage(), duration: 5000);
        }
    }

    public function destroy(int $userId): void
    {
        try {
            DB::transaction(function () use ($userId) {
                $user = User::where('company_id', $this->company_id)
                    ->where('role_id', $this->adminRoleId)
                    ->findOrFail($userId);

                $user->departments()->detach();
                $user->delete();
            });

            $this->dispatch('toast', type: 'success', title: 'Dihapus', message: 'Admin dihapus.', duration: 3000);
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Delete failed', message: $e->getMessage(), duration: 5000);
        }
    }

    /* ------------------------------------------------------------------------
     | Validation
     * --------------------------------------------------------------------- */

    protected function rules(bool $editing = false): array
    {
        $id = $editing ? $this->editingId : null;

        return [
            'full_name'                => ['required', 'string', 'max:255'],
            'email'                    => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id, 'user_id')->whereNull('deleted_at'),
            ],
            'phone_number'             => ['nullable', 'string', 'max:30'],
            'primary_department_id'    => ['nullable', 'integer', 'exists:departments,department_id'],
            'additional_departments'   => ['array'],
            'additional_departments.*' => ['integer', 'distinct', 'different:primary_department_id', 'exists:departments,department_id'],
            'password'                 => [$editing ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ];
    }

    protected function resetCreateForm(): void
    {
        $this->reset([
            'editingId',
            'full_name',
            'email',
            'phone_number',
            'password',
            'password_confirmation',
            'primary_department_id',
            'additional_departments',
        ]);

        $this->refreshDepartments();
    }

    /* ------------------------------------------------------------------------
     | Search / List
     * --------------------------------------------------------------------- */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getAdminsProperty()
    {
        if (!$this->adminRoleId) {
            $this->adminRoleId = $this->resolveAdminRoleId();
        }

        $q = User::with(['departments', 'department'])
            ->where('company_id', $this->company_id)
            ->where('role_id', $this->adminRoleId);

        if ($this->search !== '') {
            $s = "%{$this->search}%";
            $q->where(function ($qq) use ($s) {
                $qq->where('full_name', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('phone_number', 'like', $s);
            });
        }

        return $q->orderByDesc('user_id')->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.superadmin.adminmanagement', [
            'rows' => $this->admins,
        ]);
    }
}
