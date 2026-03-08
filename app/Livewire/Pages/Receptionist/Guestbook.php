<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Guestbook as GuestbookModel;
use App\Models\Department; 
use App\Models\User;

#[Layout('layouts.receptionist')]
#[Title('GuestBook')]
class Guestbook extends Component
{
    // Form fields yang diisi user
    public $name;
    public $phone_number;
    public $instansi;
    public $keperluan;
    
    // Field baru (Nullable / Optional)
    public $department_id;
    public $user_id;

    // Data Lists untuk Dropdown
    public $departments_list = [];
    public $users_list = [];

    // Field internal (diisi otomatis)
    public $date;
    public $jam_in;
    public $petugas_penjaga;

    // ---- Compatibility props (omitted for brevity, assume they exist) ----
    
    public function mount(): void
    {
        $this->date = $this->date ?: now()->format('Y-m-d');

        // Load list departemen
        if ($compId = $this->companyId()) {
            // Load departments belonging to the current user's company
            $this->departments_list = Department::where('company_id', $compId)->get();
        } else {
            // Fallback: Load all departments if company_id is null/not used
            $this->departments_list = Department::all();
        }
        
        // Load users if department_id is already set (e.g., via session or initial state)
        if ($this->department_id) {
            $this->loadUsers();
        }
    }

    // Hook: Ketika department_id berubah, load user yang sesuai
    public function updatedDepartmentId($value)
    {
        // Reset user yang dipilih sebelumnya
        $this->user_id = null; 
        $this->loadUsers($value);
    }
    
    // Helper function to load users
    private function loadUsers(?string $departmentId = null): void
    {
        $departmentId = $departmentId ?? $this->department_id;
        
        if ($departmentId) {
            // Load users based on the selected department ID
            // NOTE: Ensure User model is using 'department_id' as the foreign key column name
            $this->users_list = User::where('department_id', (int)$departmentId)->get();
        } else {
            $this->users_list = [];
        }
    }


    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'phone_number'  => ['nullable', 'string', 'max:50'],
            'instansi'      => ['nullable', 'string', 'max:255'],
            'keperluan'     => ['nullable', 'string', 'max:255'],
            // Ensures department_id and user_id are nullable
            'department_id' => ['nullable', 'exists:departments,id'], 
            'user_id'       => ['nullable', 'exists:users,id'],      
        ];
    }

    // Helper functions (omitted for brevity, assume they exist)
    private function companyId(): ?int
    {
        return Auth::user()?->company_id;
    }

    public function save(): void
    {
        $now = Carbon::now(config('app.timezone', 'Asia/Jakarta'));
        $this->date   = $now->toDateString();
        $this->jam_in = $now->format('H:i');

        $user = Auth::user();
        $this->petugas_penjaga = $user?->full_name ?? $user?->name ?? 'Petugas Receptionist';
        $companyId = $this->companyId();
        
        // 🔥 FIX FOR SQLSTATE[22007]: Converts empty string '' (from the select box) to null
        // so MySQL accepts it for an INT/Foreign Key column.
        $this->department_id = $this->department_id === '' ? null : $this->department_id;
        $this->user_id       = $this->user_id === '' ? null : $this->user_id;

        $validatedData = $this->validate();

        // Prepare data including auto-filled fields
        $entryData = array_merge($validatedData, [
            'date'              => $this->date,
            'jam_in'            => $this->jam_in,
            'petugas_penjaga'   => $this->petugas_penjaga,
            'company_id'        => $companyId, 
            'jam_out'           => null, 
        ]);

        // Saves data to the database
        GuestbookModel::create($entryData); 

        // Reset form
        $this->reset(['name', 'phone_number', 'instansi', 'keperluan', 'department_id', 'user_id']);
        // Reset user list 
        $this->users_list = []; 

        $this->dispatch('$refresh');
        $this->dispatch('toast', type: 'success', title: 'Ditambah', message: 'Guest ditambah.', duration: 3000);
        session()->flash('saved', true);
    }

    public function render()
    {
        return view('livewire.pages.receptionist.guestbook');
    }
}