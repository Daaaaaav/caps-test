<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;

#[Layout('layouts.superadmin')]
#[Title('Account Settings')]
class Account extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public string $search = '';
    public string $roleFilter = '';
    public string $departmentFilter = ''; // <--- NEW: Department Filter property

    // Create form
    public string $full_name = '';
    public string $email = '';
    public ?string $phone_number = null;
    public ?string $password = null;
    public ?string $role_key = null;
    public ?int $department_id = null;
    public string $is_agent = 'no';

    // Edit form
    public bool $modalEdit = false;
    public ?int $editingId = null;
    public string $edit_full_name = '';
    public string $edit_email = '';
    public ?string $edit_phone_number = null;
    public ?string $edit_password = null;
    public ?string $edit_role_key = null;
    public ?int $edit_department_id = null;
    public string $edit_is_agent = 'no';

    // Derived
    public int $company_id;
    public string $company_name = '-';

    public array $roles = [];
    public array $roleOptions = [];
    public $departments;

    public ?int $roleReceptionistId = null;
    public ?int $roleSuperadminId = null;
    public ?int $deptAdministrationId = null;
    public ?int $deptExecutiveId = null;

    protected $casts = [
        'modalEdit' => 'bool',
    ];

    /* ======================== Lifecycle ======================== */

    public function mount(): void
    {
        $auth = Auth::user()->loadMissing('company');
        $this->company_id   = (int) ($auth->company_id ?? 0);
        $this->company_name = optional($auth->company)->company_name ?? '-';

        $this->roles = Role::orderBy('name')
            ->get(['role_id as id', 'name'])
            ->map(fn($r) => ['id' => (int)$r->id, 'name' => (string)$r->name])
            ->toArray();

        $options = [];
        foreach ($this->roles as $role) {
            if (strtolower($role['name']) === 'user') {
                $options[] = ['key' => $role['id'] . '_no', 'name' => 'User'];
                $options[] = ['key' => $role['id'] . '_yes', 'name' => 'User (Agent)'];
            } else {
                $options[] = ['key' => $role['id'] . '_no', 'name' => $role['name']];
            }
        }
        $this->roleOptions = $options;

        $this->roleReceptionistId = Role::where('name', 'receptionist')->value('role_id');
        $this->roleSuperadminId   = Role::where('name', 'superadmin')->value('role_id');

        $this->loadDepartments();

        $this->deptAdministrationId = Department::where('company_id', $this->company_id)
            ->where('department_name', 'Administration')
            ->value('department_id');

        $this->deptExecutiveId = Department::where('company_id', '!=', 0)
            ->where('department_name', 'Executive')
            ->value('department_id');
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingDepartmentFilter(): void { $this->resetPage(); } // <--- NEW: Reset page on filter change

    private function loadDepartments(): void
    {
        $this->departments = Department::query()
            ->when($this->company_id, fn($q) => $q->where('company_id', $this->company_id))
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);
    }

    /* ======================== Validation ======================== */

    protected function createRules(): array
    {
        $roleId = null;
        if ($this->role_key) {
            [$roleId] = explode('_', $this->role_key);
        }
        $isSpecialRole = in_array($roleId, [$this->roleReceptionistId, $this->roleSuperadminId]);

        return [
            'full_name'     => ['required', 'string', 'max:255'],
            'email'         => [
                'required', 'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone_number'  => ['nullable', 'string', 'max:30'],
            'password'      => ['required', 'string', 'min:6'],
            'role_key'      => ['required', 'string', Rule::in(array_column($this->roleOptions, 'key'))],
            'department_id' => $isSpecialRole
                ? ['nullable']
                : ['required', 'integer', Rule::exists('departments', 'department_id')->where('company_id', $this->company_id)],
        ];
    }

    protected function editRules(): array
    {
        $ignoreId = $this->editingId ?? null;
        $roleId = null;
        if ($this->edit_role_key) {
            [$roleId] = explode('_', $this->edit_role_key);
        }
        $isSpecialRole = in_array($roleId, [$this->roleReceptionistId, $this->roleSuperadminId]);

        return [
            'edit_full_name' => ['required', 'string', 'max:255'],
            'edit_email' => [
                'required', 'email',
                Rule::unique('users', 'email')
                    ->ignore($ignoreId, 'user_id')
                    ->whereNull('deleted_at'),
            ],
            'edit_phone_number' => ['nullable', 'string', 'max:30'],
            'edit_role_key' => ['required', 'string', Rule::in(array_column($this->roleOptions, 'key'))],
            'edit_department_id' => $isSpecialRole
                ? ['nullable']
                : ['required', 'integer', Rule::exists('departments', 'department_id')->where('company_id', $this->company_id)],
        ];
    }

    /* ======================== Helpers ======================== */

    private function mapDeptForRole(?int $roleId): ?int
    {
        if (!$roleId) return null;

        if ($this->roleReceptionistId && $roleId === $this->roleReceptionistId) {
            return $this->deptAdministrationId ?: null; // only if exists
        }

        if ($this->roleSuperadminId && $roleId === $this->roleSuperadminId) {
            return $this->deptExecutiveId ?: null;
        }

        return null;
    }

    private function isSuperadminRole(?int $roleId): bool
    {
        return $roleId !== null && $this->roleSuperadminId !== null && (int)$roleId === (int)$this->roleSuperadminId;
    }

    /* ======================== Reactive hooks ======================== */

    public function updatedRoleKey($value): void
    {
        [$roleId] = explode('_', $value);
        $mapped = $this->mapDeptForRole((int)$roleId);
        $this->department_id = $mapped;
    }

    public function updatedEditRoleKey($value): void
    {
        [$roleId] = explode('_', $value);
        $mapped = $this->mapDeptForRole((int)$roleId);
        $this->edit_department_id = $mapped;
    }

    /* ======================== Create ======================== */

    public function store(): void
    {
        if (!$this->role_key) {
            $this->dispatch('toast', type: 'warning', title: 'Gagal', message: 'Silakan pilih role terlebih dahulu.', duration: 3000);
            return;
        }

        [$roleId, $isAgent] = explode('_', $this->role_key);
        $roleId = (int)$roleId;

        $forced = $this->mapDeptForRole($roleId);

        // Jika department belum ada untuk role receptionist/superadmin
        if (in_array($roleId, [$this->roleReceptionistId, $this->roleSuperadminId]) && !$forced) {
            $roleName = $roleId === $this->roleReceptionistId ? 'Administration' : 'Executive';
            $this->dispatch('toast', type: 'warning', title: 'Department Belum Ada', message: "Silakan buat department {$roleName} terlebih dahulu sebelum menambahkan user ini.", duration: 5000);
            return;
        }

        if ($forced) {
            $this->department_id = $forced;
        }

        $data = $this->validate($this->createRules());

        User::create([
            'full_name'     => $data['full_name'],
            'email'         => strtolower($data['email']),
            'phone_number'  => $data['phone_number'] ?? null,
            'password'      => bcrypt($this->password),
            'role_id'       => $roleId,
            'is_agent'      => $isAgent,
            'company_id'    => $this->company_id,
            'department_id' => (int)$data['department_id'],
        ]);

        $this->resetCreateForm();
        $this->dispatch('toast', type: 'success', title: 'Dibuat', message: 'User berhasil dibuat.', duration: 3000);
        $this->resetPage();
    }

    private function resetCreateForm(): void
    {
        $this->full_name = '';
        $this->email = '';
        $this->phone_number = null;
        $this->password = null;
        $this->role_key = null;
        $this->department_id = null;
        $this->resetValidation();
    }

    /* ======================== Edit ======================== */

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $u = User::where('company_id', $this->company_id)
            ->where('user_id', $id)
            ->firstOrFail();

        if ($this->isSuperadminRole($u->role_id) && $u->user_id !== Auth::id()) {
            $this->dispatch('toast', type: 'warning', title: 'Ditolak', message: 'Tidak boleh mengedit Superadmin lain.', duration: 3000);
            return;
        }

        $this->editingId = $u->user_id;
        $this->edit_full_name = $u->full_name;
        $this->edit_email = $u->email;
        $this->edit_phone_number = $u->phone_number;
        $this->edit_role_key = $u->role_id . '_' . ($u->is_agent ?? 'no');
        $this->edit_department_id = $u->department_id;
        $this->edit_password = null;

        $this->modalEdit = true;
    }

    public function closeEdit(): void
    {
        $this->modalEdit = false;
        $this->editingId = null;
        $this->edit_full_name = '';
        $this->edit_email = '';
        $this->edit_phone_number = null;
        $this->edit_role_key = null;
        $this->edit_department_id = null;
        $this->edit_password = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function update(): void
    {
        if (!$this->editingId) return;

        $u = User::where('company_id', $this->company_id)
            ->where('user_id', $this->editingId)
            ->firstOrFail();

        if ($this->isSuperadminRole($u->role_id) && $u->user_id !== Auth::id()) {
            $this->dispatch('toast', type: 'warning', title: 'Ditolak', message: 'Tidak boleh mengedit Superadmin lain.', duration: 3000);
            return;
        }

        [$roleId, $isAgent] = explode('_', $this->edit_role_key);
        $roleId = (int)$roleId;

        // Cek department untuk role tertentu
        if (in_array($roleId, [$this->roleReceptionistId, $this->roleSuperadminId])) {
            $forced = $this->mapDeptForRole($roleId);
            if (!$forced) {
                $roleName = $roleId === $this->roleReceptionistId ? 'Administration' : 'Executive';
                $this->dispatch('toast', type: 'warning', title: 'Department Belum Ada', message: "Silakan buat department {$roleName} terlebih dahulu sebelum mengubah user ini.", duration: 5000);
                return;
            }
            $this->edit_department_id = $forced;
        }

        $data = $this->validate($this->editRules());

        $payload = [
            'full_name' => $data['edit_full_name'],
            'email' => strtolower($data['edit_email']),
            'phone_number' => $data['edit_phone_number'] ?? null,
            'role_id' => $roleId,
            'is_agent' => $isAgent,
            'department_id' => (int)$data['edit_department_id'],
        ];

        if (!empty($this->edit_password)) {
            $payload['password'] = bcrypt($this->edit_password);
        }

        $u->update($payload);

        $this->closeEdit();
        $this->dispatch('toast', type: 'success', title: 'Diupdate', message: 'User diupdate.', duration: 3000);
    }

    /* ======================== Delete ======================== */

    public function delete(int $id): void
    {
        $u = User::where('company_id', $this->company_id)
            ->where('user_id', $id)
            ->firstOrFail();

        if ($this->isSuperadminRole($u->role_id)) {
            $this->dispatch('toast', type: 'warning', title: 'Ditolak', message: 'Tidak boleh menghapus akun Superadmin.', duration: 3000);
            return;
        }

        $u->delete();

        if ($this->editingId === $id) {
            $this->closeEdit();
        }

        $this->dispatch('toast', type: 'success', title: 'Dihapus', message: 'User dihapus (soft delete).', duration: 3000);
    }

    /* ======================== Render ======================== */

    public function render()
    {
        $users = User::with(['role', 'department'])
            ->where('company_id', $this->company_id)
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('full_name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->when($this->roleFilter, fn($q) => $q->where('role_id', (int)$this->roleFilter))
            ->when($this->departmentFilter, fn($q) => $q->where('department_id', (int)$this->departmentFilter)) // <--- NEW: Apply department filter
            ->orderByDesc('user_id')
            ->paginate(10);

        return view('livewire.pages.superadmin.account', [
            'users' => $users,
            'departments' => $this->departments,
        ]);
    }
}