<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use App\Models\Wifi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Admin - WiFi Management')]
class WifiManagement extends Component
{
    use WithPagination;

    // Properties untuk Create Form
    public $ssid;
    public $password;
    public $location;
    public $is_active = true;

    // Properties khusus untuk Edit Form (agar tidak bentrok)
    public $wifi_id;
    public $edit_ssid;
    public $edit_password;
    public $edit_location;
    public $edit_is_active;

    // UI State
    public $modalEdit = false;
    public $search = '';

    /**
     * Permission Check (Logic Anda)
     */
    public function mount()
    {
        $user = Auth::user();

        if (!$user->relationLoaded('department')) {
            $user->load('department');
        }

        $isAdmin = $user->role_id == 2;
        $isIT = $user->department && $user->department->department_name === 'IT';

        if (!$isAdmin || !$isIT) {
            abort(403, 'ACCESS DENIED: restricted to IT Admins only.');
        }
    }

    protected $rules = [
        'ssid' => 'required|string|max:255',
        'password' => 'required|string|max:255',
        'location' => 'nullable|string|max:255',
        'is_active' => 'boolean',
    ];

    public function resetInputFields()
    {
        $this->ssid = '';
        $this->password = '';
        $this->location = '';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function store()
    {
        $this->validate();

        Wifi::create([
            'company_id' => Auth::user()->company_id,
            'ssid' => $this->ssid,
            'password' => $this->password,
            'location' => $this->location,
            'is_active' => $this->is_active ? 1 : 0,
        ]);

        // **MODIFIED:** Menggunakan dispatch toast
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'WiFi berhasil ditambahkan.', duration: 2500); 
        $this->resetInputFields();
    }

    // Membuka Modal Edit & Mengisi Data
    public function openEdit($id)
    {
        $wifi = Wifi::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $this->wifi_id = $id;
        $this->edit_ssid = $wifi->ssid;
        $this->edit_password = $wifi->password;
        $this->edit_location = $wifi->location;
        $this->edit_is_active = (bool) $wifi->is_active;
        
        $this->modalEdit = true;
        $this->resetErrorBag();
    }

    // Menutup Modal Edit
    public function closeEdit()
    {
        $this->modalEdit = false;
        $this->reset(['wifi_id', 'edit_ssid', 'edit_password', 'edit_location', 'edit_is_active']);
    }

    public function update()
    {
        $this->validate([
            'edit_ssid' => 'required|string|max:255',
            'edit_password' => 'required|string|max:255',
            'edit_location' => 'nullable|string|max:255',
            'edit_is_active' => 'boolean',
        ]);

        if ($this->wifi_id) {
            $wifi = Wifi::where('company_id', Auth::user()->company_id)->find($this->wifi_id);
            
            if($wifi) {
                $wifi->update([
                    'ssid' => $this->edit_ssid,
                    'password' => $this->edit_password,
                    'location' => $this->edit_location,
                    'is_active' => $this->edit_is_active ? 1 : 0,
                ]);
                
                // **MODIFIED:** Menggunakan dispatch toast
                $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'WiFi berhasil diperbarui.', duration: 2500);
                $this->closeEdit();
            }
        }
    }

    public function delete($id)
    {
        $wifi = Wifi::where('company_id', Auth::user()->company_id)->find($id);
        
        if ($wifi) {
            $wifi->delete();
            // **MODIFIED:** Menggunakan dispatch toast
            $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'WiFi berhasil dihapus.', duration: 2500);
        }
    }

    public function render()
    {
        $query = Wifi::where('company_id', Auth::user()->company_id);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('ssid', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        $wifis = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.pages.admin.wifimanagement', [
            'wifis' => $wifis,
            'company_name' => Auth::user()->company->company_name ?? 'Company',
            'department_name' => Auth::user()->department->department_name ?? 'Department'
        ]);
    }
}