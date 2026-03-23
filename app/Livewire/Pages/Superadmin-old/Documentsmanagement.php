<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery;

#[Layout('layouts.superadmin')]
#[Title('Documents Management')]
class Documentsmanagement extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'tailwind';

    public int $company_id = 0;
    public ?int $department_id = null;
    public ?int $user_id = null;

    // Filters
    public string $search = '';
    public bool $showTrashed = false;
    public ?string $filterStatus = null; // optional
    public ?string $filterType = null;   // optional: document|invoice|tel

    // Create form
    public string $item_name = '';
    public string $type = 'document'; // document|invoice|tel (NO package)
    public ?string $nama_pengirim = null;
    public ?string $nama_penerima = null;
    public ?int $storage_id = null;
    public ?string $pengiriman = null;   // datetime-local
    public ?string $pengambilan = null;  // datetime-local
    public string $status = 'pending';    // pending|stored|taken|delivered

    // Edit modal
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public string $edit_item_name = '';
    public string $edit_type = 'document';
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
        $this->resetPage(pageName: 'docsPage');
    }

    /** Query dasar: selain type = package */
    protected function baseScope()
    {
        return Delivery::query()
            ->where('company_id', $this->company_id)
            ->where(function ($q) {
                $q->where('department_id', $this->department_id)
                    ->orWhereNull('department_id');
            })
            ->where('type', '!=', 'package');
    }

    /** CREATE */
    public function create(): void
    {
        $this->validate([
            'item_name' => 'required|string|max:255',
            'type' => 'required|in:document,invoice,tel', // no package
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
            'type' => $this->type, // doc|invoice|tel
            'nama_pengirim' => trim($this->nama_pengirim ?? ''),
            'nama_penerima' => trim($this->nama_penerima ?? ''),
            'storage_id' => $this->storage_id,
            'pengiriman' => $this->pengiriman,
            'pengambilan' => $this->pengambilan,
            'status' => $this->status,
        ]);

        $this->reset([
            'item_name',
            'type',
            'nama_pengirim',
            'nama_penerima',
            'storage_id',
            'pengiriman',
            'pengambilan',
            'status'
        ]);
        $this->type = 'document';
        $this->status = 'pending';

        session()->flash('ok', 'Document entry created.');
        $this->resetPage(pageName: 'docsPage');
    }

    /** OPEN EDIT */
    public function openEdit(int $id): void
    {
        $row = $this->baseScope()->withTrashed()->findOrFail($id);

        $this->edit_id = $row->delivery_id;
        $this->edit_item_name = $row->item_name;
        $this->edit_type = $row->type; // ensured != package
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
            'edit_type' => 'required|in:document,invoice,tel', // still no package
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
                'type' => $this->edit_type,
                'nama_pengirim' => trim($this->edit_nama_pengirim ?? ''),
                'nama_penerima' => trim($this->edit_nama_penerima ?? ''),
                'storage_id' => $this->edit_storage_id,
                'pengiriman' => $this->edit_pengiriman,
                'pengambilan' => $this->edit_pengambilan,
                'status' => $this->edit_status,
            ]);

        $this->modalEdit = false;
        session()->flash('ok', 'Document entry updated.');
    }

    /** DELETE (soft) */
    public function delete(int $id): void
    {
        $this->baseScope()->findOrFail($id)->delete();
        session()->flash('ok', 'Entry moved to trash.');
        $this->resetPage(pageName: 'docsPage');
    }

    /** RESTORE */
    public function restore(int $id): void
    {
        $row = $this->baseScope()->withTrashed()->findOrFail($id);
        if ($row->trashed()) {
            $row->restore();
            session()->flash('ok', 'Entry restored.');
        }
    }

    /** FORCE DELETE */
    public function forceDelete(int $id): void
    {
        $row = $this->baseScope()->withTrashed()->findOrFail($id);
        $row->forceDelete();
        session()->flash('ok', 'Entry permanently deleted.');
        $this->resetPage(pageName: 'docsPage');
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

        if ($this->filterType) {
            $q->where('type', $this->filterType); // still != package due to baseScope
        }

        if (trim($this->search) !== '') {
            $s = trim($this->search);
            $q->where(function ($x) use ($s) {
                $x->where('item_name', 'like', "%{$s}%")
                    ->orWhere('nama_pengirim', 'like', "%{$s}%")
                    ->orWhere('nama_penerima', 'like', "%{$s}%");
            });
        }

        $rows = $q->orderByDesc('created_at')->paginate(10, ['*'], 'docsPage');

        return view('livewire.pages.superadmin.documentsmanagement', [
            'rows' => $rows,
        ]);
    }
}
