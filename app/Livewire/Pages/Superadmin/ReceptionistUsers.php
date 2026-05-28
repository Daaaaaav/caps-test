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
    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */
    public $search = '';
    public $statusFilter = 'all';

    /*
    |--------------------------------------------------------------------------
    | MODAL / CRUD STATE
    |--------------------------------------------------------------------------
    */
    public $showModal = false;
    public $editMode = false;
    public $userId = null;

    /*
    |--------------------------------------------------------------------------
    | FORM FIELDS
    |--------------------------------------------------------------------------
    */
    public $name = '';
    public $email = '';
    public $password = '';
    public $phone = '';
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
        try {
            $user = User::where('user_id', $id)->firstOrFail();

            $this->userId = $user->user_id;
            $this->name = $user->full_name;
            $this->email = $user->email;
            $this->phone = $user->phone_number;
            $this->status = $user->status ?? 'active';
            $this->password = '';

            $this->editMode = true;
            $this->showModal = true;

        } catch (\Exception $e) {

            $this->dispatch(
                'toast',
                type: 'error',
                title: 'Error',
                message: 'Failed to load user data: ' . $e->getMessage(),
                duration: 4000
            );
        }
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
        $this->phone = '';
        $this->status = 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RULES
    |--------------------------------------------------------------------------
    */
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',

            'email' =>
                'required|email|max:255|unique:users,email,' .
                ($this->userId ?? 'NULL') .
                ',user_id',

            'phone' => 'nullable|string|max:20',

            'password' => $this->editMode
                ? 'nullable|min:6'
                : 'required|min:6',

            'status' => 'required|in:active,inactive',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE (CREATE / UPDATE)
    |--------------------------------------------------------------------------
    */
    public function save()
    {
        $this->validate();

        try {

            $companyId = Auth::user()->company_id;

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */
            if ($this->editMode) {

                $user = User::where('user_id', $this->userId)->firstOrFail();

                $user->full_name = $this->name;
                $user->email = $this->email;
                $user->phone_number = $this->phone;
                $user->status = $this->status;

                // Only update password if filled
                if (!empty($this->password)) {
                    $user->password = Hash::make($this->password);
                }

                $user->save();

                $this->dispatch(
                    'toast',
                    type: 'success',
                    title: 'Success',
                    message: 'Receptionist updated successfully!',
                    duration: 3000
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | CREATE
                |--------------------------------------------------------------------------
                */
                $role = Role::where('name', 'Receptionist')->first();

                if (!$role) {
                    throw new \Exception(
                        'Receptionist role not found in database'
                    );
                }

                User::create([
                    'full_name'    => $this->name,
                    'email'        => $this->email,
                    'password'     => Hash::make($this->password),
                    'phone_number' => $this->phone ?: '-',
                    'status'       => $this->status,
                    'company_id'   => $companyId,
                    'role_id'      => $role->role_id,
                    'is_agent'     => 'no',
                ]);

                $this->dispatch(
                    'toast',
                    type: 'success',
                    title: 'Success',
                    message: 'Receptionist created successfully!',
                    duration: 3000
                );
            }

            $this->closeModal();

        } catch (\Exception $e) {

            $this->dispatch(
                'toast',
                type: 'error',
                title: 'Error',
                message: 'Failed to save receptionist: ' . $e->getMessage(),
                duration: 4000
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        try {

            $user = User::where('user_id', $id)->firstOrFail();

            // Prevent deleting yourself
            if ($user->user_id === Auth::id()) {
                $this->dispatch(
                    'toast',
                    type: 'error',
                    title: 'Error',
                    message: 'You cannot delete your own account.',
                    duration: 4000
                );
                return;
            }

            $user->delete();

            $this->dispatch(
                'toast',
                type: 'success',
                title: 'Success',
                message: 'Receptionist deleted successfully!',
                duration: 3000
            );

        } catch (\Exception $e) {

            $this->dispatch(
                'toast',
                type: 'error',
                title: 'Error',
                message: 'Failed to delete receptionist: ' . $e->getMessage(),
                duration: 4000
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        try {

            $companyId = Auth::user()->company_id;

            $query = User::query()
                ->where('company_id', $companyId)
                ->whereHas('role', function ($q) {
                    $q->where('roles.name', 'Receptionist');
                });

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */
            if (!empty($this->search)) {

                $query->where(function ($q) {
                    $q->where('users.full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('users.email', 'like', '%' . $this->search . '%')
                      ->orWhere('users.phone_number', 'like', '%' . $this->search . '%');
                });
            }

            /*
            |--------------------------------------------------------------------------
            | STATUS FILTER
            |--------------------------------------------------------------------------
            */
            if ($this->statusFilter !== 'all') {
                $query->where('status', $this->statusFilter);
            }

            $receptionists = $query
                ->latest()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | STATS
            |--------------------------------------------------------------------------
            */
            $stats = [
                [
                    'label' => 'Total Receptionists',
                    'value' => $receptionists->count(),
                    'key' => 'all',
                ],
                [
                    'label' => 'Active',
                    'value' => $receptionists
                        ->where('status', 'active')
                        ->count(),
                    'key' => 'active',
                ],
                [
                    'label' => 'Inactive',
                    'value' => $receptionists
                        ->where('status', 'inactive')
                        ->count(),
                    'key' => 'inactive',
                ],
            ];

            return view(
                'livewire.pages.superadmin.receptionist-users',
                [
                    'receptionists' => $receptionists,
                    'stats' => $stats,
                ]
            );

        } catch (\Exception $e) {

            $this->dispatch(
                'toast',
                type: 'error',
                title: 'Error',
                message: 'Failed to retrieve data: ' . $e->getMessage(),
                duration: 4000
            );

            return view(
                'livewire.pages.superadmin.receptionist-users',
                [
                    'receptionists' => collect([]),

                    'stats' => [
                        [
                            'label' => 'Total Receptionists',
                            'value' => 0,
                            'key' => 'all',
                        ],
                        [
                            'label' => 'Active',
                            'value' => 0,
                            'key' => 'active',
                        ],
                        [
                            'label' => 'Inactive',
                            'value' => 0,
                            'key' => 'inactive',
                        ],
                    ],
                ]
            );
        }
    }
}
