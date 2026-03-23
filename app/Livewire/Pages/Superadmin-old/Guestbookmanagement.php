<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Guestbook;

#[Layout('layouts.superadmin')]
#[Title('Guestbook Management')]
class Guestbookmanagement extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'tailwind';

    public int $company_id = 0;
    public ?int $department_id = null; // <<< nullable

    // Filters
    public string $search = '';
    public bool $showTrashed = false;

    // Create form
    public $date, $jam_in, $jam_out, $name, $phone_number, $instansi, $keperluan, $petugas_penjaga;

    // Edit modal
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public $edit_date, $edit_jam_in, $edit_jam_out, $edit_name, $edit_phone_number, $edit_instansi, $edit_keperluan, $edit_petugas_penjaga;

    public function mount(): void
    {
        $user = Auth::user();
        $this->company_id = (int) ($user->company_id ?? 0);
        $this->department_id = $user->department_id ?? null; // <<< biarkan null
    }

    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'guestbooksPage');
    }

    public function create(): void
    {
        $this->validate([
            'date' => 'required|date',
            'jam_in' => 'required',
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'instansi' => 'nullable|string|max:255',
            'keperluan' => 'nullable|string|max:255',
            'petugas_penjaga' => 'nullable|string|max:255',
        ]);

        Guestbook::create([
            'company_id' => $this->company_id,
            'department_id' => $this->department_id, // boleh null
            'date' => $this->date,
            'jam_in' => $this->jam_in,
            'jam_out' => $this->jam_out,
            'name' => trim($this->name),
            'phone_number' => trim($this->phone_number ?? ''),
            'instansi' => trim($this->instansi ?? ''),
            'keperluan' => trim($this->keperluan ?? ''),
            'petugas_penjaga' => trim($this->petugas_penjaga ?? ''),
        ]);

        $this->reset(['date', 'jam_in', 'jam_out', 'name', 'phone_number', 'instansi', 'keperluan', 'petugas_penjaga']);
        session()->flash('ok', 'Guestbook entry added.');
        $this->resetPage(pageName: 'guestbooksPage');
    }

    public function openEdit(int $id): void
    {
        $row = Guestbook::withTrashed()
            ->where('company_id', $this->company_id)
            ->where(function ($q) { // <<< departemen cocok atau null
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->findOrFail($id);

        $this->edit_id = $row->guestbook_id;
        $this->edit_date = optional($row->date)->format('Y-m-d');
        $this->edit_jam_in = is_string($row->jam_in) ? substr($row->jam_in, 0, 5) : $row->jam_in;
        $this->edit_jam_out = is_string($row->jam_out) ? substr($row->jam_out, 0, 5) : $row->jam_out;
        $this->edit_name = $row->name;
        $this->edit_phone_number = $row->phone_number;
        $this->edit_instansi = $row->instansi;
        $this->edit_keperluan = $row->keperluan;
        $this->edit_petugas_penjaga = $row->petugas_penjaga;

        $this->modalEdit = true;
    }

    public function update(): void
    {
        $this->validate([
            'edit_date' => 'required|date',
            'edit_jam_in' => 'required',
            'edit_name' => 'required|string|max:255',
            'edit_phone_number' => 'nullable|string|max:50',
            'edit_instansi' => 'nullable|string|max:255',
            'edit_keperluan' => 'nullable|string|max:255',
            'edit_petugas_penjaga' => 'nullable|string|max:255',
        ]);

        if (!$this->edit_id)
            return;

        Guestbook::where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->where('guestbook_id', $this->edit_id)
            ->update([
                'date' => $this->edit_date,
                'jam_in' => $this->edit_jam_in,
                'jam_out' => $this->edit_jam_out,
                'name' => trim($this->edit_name),
                'phone_number' => trim($this->edit_phone_number ?? ''),
                'instansi' => trim($this->edit_instansi ?? ''),
                'keperluan' => trim($this->edit_keperluan ?? ''),
                'petugas_penjaga' => trim($this->edit_petugas_penjaga ?? ''),
            ]);

        $this->modalEdit = false;
        session()->flash('ok', 'Guestbook updated.');
    }

    public function delete(int $id): void
    {
        Guestbook::where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->findOrFail($id)
            ->delete();

        session()->flash('ok', 'Guestbook moved to trash.');
        $this->resetPage(pageName: 'guestbooksPage');
    }

    public function restore(int $id): void
    {
        $row = Guestbook::withTrashed()
            ->where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->findOrFail($id);

        if ($row->trashed()) {
            $row->restore();
            session()->flash('ok', 'Guestbook restored.');
        }
    }

    public function forceDelete(int $id): void
    {
        $row = Guestbook::withTrashed()
            ->where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->findOrFail($id);

        $row->forceDelete();
        session()->flash('ok', 'Guestbook permanently deleted.');
        $this->resetPage(pageName: 'guestbooksPage');
    }

    public function render()
    {
        $query = Guestbook::query()
            ->where('company_id', $this->company_id)
            ->where(function ($q) { // <<< inti perbaikan
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            });

        if (!$this->showTrashed) {
            $query->whereNull('deleted_at');
        }

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('instansi', 'like', "%{$s}%")
                    ->orWhere('keperluan', 'like', "%{$s}%")
                    ->orWhere('phone_number', 'like', "%{$s}%");
            });
        }

        $rows = $query->orderByDesc('created_at')
            ->paginate(10, ['*'], 'guestbooksPage');

        return view('livewire.pages.superadmin.guestbookmanagement', [
            'rows' => $rows,
        ]);
    }
}
