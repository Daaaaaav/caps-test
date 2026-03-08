<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement as AnnouncementModel;

#[Layout('layouts.superadmin')]
#[Title('Announcement')]
class Announcement extends Component
{
    use WithPagination;

    // Derived from Auth
    public ?int $company_id = null;

    // Table filter & sorting
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public bool $showTrashed = false; // <— toggle lihat trash

    // Create form fields
    public string $description = '';
    public ?string $event_at = null;

    // Edit modal state & fields
    public bool $modalEdit = false;
    public ?int $edit_id = null;
    public string $edit_description = '';
    public ?string $edit_event_at = null;

    protected function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'event_at' => 'nullable|date',
        ];
    }

    protected function editRules(): array
    {
        return [
            'edit_description' => 'required|string|max:255',
            'edit_event_at' => 'nullable|date',
        ];
    }

    public function mount(): void
    {
        $this->company_id = Auth::user()?->company_id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShowTrashed(): void
    {
        $this->resetPage();
    }

    public function store(): void
    {
        $this->validate();

        AnnouncementModel::create([
            'company_id' => $this->company_id,
            'description' => $this->description,
            'event_at' => $this->event_at ? Carbon::parse($this->event_at) : null,
        ]);

        $this->reset('description', 'event_at');
        $this->dispatch('toast', type: 'success', title: 'Dibuat', message: 'Announcement berhasil dibuat.', duration: 3000);
        $this->resetPage();
    }

    public function openEdit(int $id): void
    {
        $query = AnnouncementModel::where('company_id', $this->company_id);
        if ($this->showTrashed) {
            $query->withTrashed();
        }

        $announcement = $query->findOrFail($id);

        $this->edit_id = $announcement->getKey();
        $this->edit_description = $announcement->description;
        $this->edit_event_at = $announcement->event_at ? $announcement->event_at->format('Y-m-d\TH:i') : null;

        $this->modalEdit = true;
        $this->resetErrorBag();
    }

    public function closeEdit(): void
    {
        $this->modalEdit = false;
        $this->reset('edit_id', 'edit_description', 'edit_event_at');
    }

    public function update(): void
    {
        $this->validate($this->editRules());

        $query = AnnouncementModel::where('company_id', $this->company_id);
        if ($this->showTrashed) {
            $query->withTrashed();
        }

        $announcement = $query->findOrFail($this->edit_id);

        $announcement->update([
            'description' => $this->edit_description,
            'event_at' => $this->edit_event_at ? Carbon::parse($this->edit_event_at) : null,
        ]);

        $this->closeEdit();
        $this->dispatch('toast', type: 'success', title: 'Diupdate', message: 'Announcement berhasil diupdate.', duration: 3000);
    }

    /**
     * Soft Delete (hapus ke trash).
     */
    public function delete(int $id): void
    {
        AnnouncementModel::where('company_id', $this->company_id)
            ->findOrFail($id)
            ->delete(); // <-- soft delete otomatis

        $this->dispatch('toast', type: 'success', title: 'Dihapus', message: 'Announcement dipindah ke Trash.', duration: 3000);
        $this->resetPage();
    }

    /**
     * Restore dari trash.
     */
    public function restore(int $id): void
    {
        $announcement = AnnouncementModel::onlyTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        $announcement->restore();

        $this->dispatch('toast', type: 'success', title: 'Dipulihkan', message: 'Announcement berhasil dipulihkan.', duration: 3000);
        $this->resetPage();
    }

    /**
     * Hapus permanen (opsional).
     */
    public function forceDelete(int $id): void
    {
        $announcement = AnnouncementModel::onlyTrashed()
            ->where('company_id', $this->company_id)
            ->findOrFail($id);

        $announcement->forceDelete();

        $this->dispatch('toast', type: 'success', title: 'Dihapus Permanen', message: 'Announcement dihapus permanen.', duration: 3000);
        $this->resetPage();
    }

    public function render()
    {
        $base = AnnouncementModel::query()
            ->where('company_id', $this->company_id)
            ->when(
                $this->search,
                fn($q) =>
                $q->where('description', 'like', "%{$this->search}%")
            );

        // Jika ingin lihat item trash saja
        if ($this->showTrashed) {
            $base->onlyTrashed();
        }

        $announcements = $base
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.pages.superadmin.announcement', compact('announcements'));
    }
}
