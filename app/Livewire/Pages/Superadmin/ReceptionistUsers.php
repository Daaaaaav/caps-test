<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

#[Layout('layouts.superadmin')]
#[Title('Receptionist Users')]
class ReceptionistUsers extends Component
{
    public $search = '';
    public $statusFilter = 'all';
    
    // CRUD properties
    public $showModal = false;
    public $editMode = false;
    public $userId = null;

    public $name = '';
    public $email = '';
    public $password = '';
    public $status = 'active';

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */
    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
    }

    /*
    |--------------------------------------------------------------------------
    | MODAL CONTROL
    |--------------------------------------------------------------------------
    */
    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();

        $this->userId = $user->user_id;
        $this->name = $user->fullname;
        $this->email = $user->email;
        $this->status = $user->status ?? 'active';
        $this->password = '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->status = 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE (CREATE / UPDATE)
    |--------------------------------------------------------------------------
    */
    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($this->userId ?? 'NULL') . ',user_id',
            'password' => $this->editMode ? 'nullable|min:6' : 'required|min:6',
            'status' => 'required|in:active,inactive',
        ]);

        $companyId = Auth::user()->company_id;

        if ($this->editMode) {
            $user = User::where('user_id', $this->userId)->firstOrFail();

            // ✅ Explicit assignment (more reliable than update array)
            $user->name = $this->name;
            $user->email = $this->email;
            $user->status = $this->status;

            // Only update password if provided
            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }

            $user->save();

            session()->flash('message', 'Receptionist updated successfully!');
        } else {
            $role = Role::where('name', 'Receptionist')->first();

            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => $this->status,
                'company_id' => $companyId,
                'role_id' => $role?->role_id,
            ]);

            session()->flash('message', 'Receptionist created successfully!');
        }

        $this->closeModal();
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        User::where('user_id', $id)->delete();

        session()->flash('message', 'Receptionist deleted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        $companyId = Auth::user()->company_id;

        $query = User::query()
            ->where('company_id', $companyId)
            ->whereHas('role', fn($q) => $q->where('name', 'Receptionist'));

        // SEARCH
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // STATUS FILTER (apply BEFORE get() → better performance)
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $receptionists = $query->get();

        // STATS (separate clean calculation)
        $stats = [
            [
                'label' => 'Total Receptionists',
                'value' => $receptionists->count(),
                'key' => 'all'
            ],
            [
                'label' => 'Active',
                'value' => $receptionists->where('status', 'active')->count(),
                'key' => 'active'
            ],
            [
                'label' => 'Inactive',
                'value' => $receptionists->where('status', 'inactive')->count(),
                'key' => 'inactive'
            ],
        ];

        return view('livewire.pages.superadmin.receptionist-users', [
            'receptionists' => $receptionists,
            'stats' => $stats,
        ]);
    }
}