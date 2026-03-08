<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery;

#[Layout('layouts.superadmin')]
#[Title('Package Management')]
class Packagemanagement extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'tailwind';

    public int $company_id = 0;
    public ?int $department_id = null;
    public ?int $user_id = null;

    // Filters
    public string $search = '';
    public bool $showTrashed = false;
    public ?string $filterStatus = null; // optional filter

    // Create form
    public string $item_name = '';
    public ?string $nama_pengirim = null;
    public ?string $nama_penerima = null;
    public ?int $storage_id = null;
    public ?string $pengiriman = null;  // 'YYYY-MM-DDTHH:MM'
    public ?string $pengambilan = null; // 'YYYY-MM-DDTHH:MM'
    public string $status = 'pending';  // pending|stored|taken|delivered

    // Edit modal
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public string $edit_item_name = '';
    public ?string $edit_nama_pengirim = null;
    public ?string $edit_nama_penerima = null;
    public ?int $edit_storage_id = null;
    public ?string $edit_pengiriman = null;
    public ?string $edit_pengambilan = null;
    public string $edit_status = 'pending';

    public function mount(): void
    {
        $u = Auth::user();
        $this->company_id = (int) ($u->company_id ?? 0);
        $this->department_id = $u->department_id ?? null;
        $this->user_id = (int) ($u->id ?? 0);
    }

    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'packagesPage');
    }

    protected function baseScope()
    {
        // Selalu filter ke company + (department_id yg sama ATAU null) + type=package
        return Delivery::query()
            ->where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->where('type', 'package');
    }

    /** CREATE */
    public function create(): void
    {
        $this->validate([
            'item_name' => 'required|string|max:255',
            'nama_pengirim' => 'nullable|string|max:255',
            'nama_penerima' => 'nullable|string|max:255',
            'storage_id' => 'nullable|integer',
            'pengiriman' => 'nullable|date',
            'pengambilan' => 'nullable|date|after_or_equal:pengiriman',
            'status' => 'required|in:pending,stored,taken,delivered',
        ]);

        Delivery::create([
            'company_id' => $this->company_id,
            'department_id' => $this->department_id,
            'receptionist_id' => $this->user_id,
            'item_name' => trim($this->item_name),
            'type' => 'package',
            'nama_pengirim' => trim($this->nama_pengirim ?? ''),
            'nama_penerima' => trim($this->nama_penerima ?? ''),
            'storage_id' => $this->storage_id,
            'pengiriman' => $this->pengiriman,
            'pengambilan' => $this->pengambilan,
            'status' => $this->status,
        ]);

        $this->reset([
            'item_name',
            'nama_pengirim',
            'nama_penerima',
            'storage_id',
            'pengiriman',
            'pengambilan',
            'status'
        ]);
        $this->status = 'pending';

        session()->flash('ok', 'Package created.');
        $this->resetPage(pageName: 'packagesPage');
    }

    /** OPEN EDIT */
    public function openEdit(int $id): void
    {
        $row = $this->baseScope()->withTrashed()->findOrFail($id);

        $this->edit_id = $row->delivery_id;
        $this->edit_item_name = $row->item_name;
        $this->edit_nama_pengirim = $row->nama_pengirim;
        $this->edit_nama_penerima = $row->nama_penerima;
        $this->edit_storage_id = $row->storage_id;
        $this->edit_pengiriman = optional($row->pengiriman)->format('Y-m-d\TH:i');
        $this->edit_pengambilan = optional($row->pengambilan)->format('Y-m-d\TH:i');
        $this->edit_status = $row->status ?? 'pending';

        $this->modalEdit = true;
    }

    /** UPDATE */
    public function update(): void
    {
        $this->validate([
            'edit_item_name' => 'required|string|max:255',
            'edit_nama_pengirim' => 'nullable|string|max:255',
            'edit_nama_penerima' => 'nullable|string|max:255',
            'edit_storage_id' => 'nullable|integer',
            'edit_pengiriman' => 'nullable|date',
            'edit_pengambilan' => 'nullable|date|after_or_equal:edit_pengiriman',
            'edit_status' => 'required|in:pending,stored,taken,delivered',
        ]);

        if (!$this->edit_id)
            return;

        $this->baseScope()
            ->where('delivery_id', $this->edit_id)
            ->update([
                'item_name' => trim($this->edit_item_name),
                'nama_pengirim' => trim($this->edit_nama_pengirim ?? ''),
                'nama_penerima' => trim($this->edit_nama_penerima ?? ''),
                'storage_id' => $this->edit_storage_id,
                'pengiriman' => $this->edit_pengiriman,
                'pengambilan' => $this->edit_pengambilan,
                'status' => $this->edit_status,
            ]);

        $this->modalEdit = false;
        session()->flash('ok', 'Package updated.');
    }

    /** DELETE (soft) */
    public function delete(int $id): void
    {
        $this->baseScope()->findOrFail($id)->delete();
        session()->flash('ok', 'Package moved to trash.');
        $this->resetPage(pageName: 'packagesPage');
    }

    /** RESTORE */
    public function restore(int $id): void
    {
        $row = $this->baseScope()->withTrashed()->findOrFail($id);
        if ($row->trashed()) {
            $row->restore();
            session()->flash('ok', 'Package restored.');
        }
    }

    /** FORCE DELETE */
    public function forceDelete(int $id): void
    {
        $row = $this->baseScope()->withTrashed()->findOrFail($id);
        $row->forceDelete();
        session()->flash('ok', 'Package permanently deleted.');
        $this->resetPage(pageName: 'packagesPage');
    }

    public function render()
    {
        $q = $this->baseScope();

        if (!$this->showTrashed) {
            $q->whereNull('deleted_at');
        }

        if ($this->filterStatus) {
            $q->where('status', $this->filterStatus);
        }

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $q->where(function ($x) use ($s) {
                $x->where('item_name', 'like', "%{$s}%")
                    ->orWhere('nama_pengirim', 'like', "%{$s}%")
                    ->orWhere('nama_penerima', 'like', "%{$s}%");
            });
        }

        $rows = $q->orderByDesc('created_at')->paginate(10, ['*'], 'packagesPage');

        return view('livewire.pages.superadmin.packagemanagement', [
            'rows' => $rows,
        ]);
    }
}
