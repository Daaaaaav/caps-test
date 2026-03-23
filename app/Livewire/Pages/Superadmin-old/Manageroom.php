<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Room;

#[Layout('layouts.superadmin')]
#[Title('Manage Room')]
class Manageroom extends Component
{
    use WithPagination;

    public int $companyId;

    // Create
    public string $room_name = '';
    public $capacity = null;            // <— untyped to accept '' from input
    public string $search = '';

    // Edit modal
    public bool $roomModal = false;
    public ?int $room_edit_id = null;
    public string $room_edit_name = '';
    public $room_edit_capacity = null;  // <— untyped

    public function mount(): void
    {
        $this->companyId = (int) (Auth::user()->company_id ?? 0);
    }

    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'roomsPage');
    }

    protected function roomCreateRules(): array
    {
        return [
            'room_name' => [
                'required', 'string', 'max:255',
                Rule::unique('rooms', 'room_name')
                    ->where(fn($q) => $q->where('company_id', $this->companyId)),
            ],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function roomEditRules(): array
    {
        return [
            'room_edit_name' => [
                'required', 'string', 'max:255',
                Rule::unique('rooms', 'room_name')
                    ->where(fn($q) => $q->where('company_id', $this->companyId))
                    ->ignore($this->room_edit_id, 'room_id'),
            ],
            'room_edit_capacity' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    // Create
    public function roomStore(): void
    {
        $this->validate($this->roomCreateRules());

        Room::create([
            'company_id' => $this->companyId,
            'room_name'  => $this->room_name,
            'capacity'   => $this->capacity === '' ? null : (int) $this->capacity, // <— coerce
        ]);

        $this->reset('room_name', 'capacity');
        session()->flash('success', 'Room berhasil dibuat.');
        $this->resetPage(pageName: 'roomsPage');
    }

    // Edit
    public function roomOpenEdit(int $id): void
    {
        $r = Room::where('company_id', $this->companyId)->findOrFail($id);
        $this->room_edit_id       = $r->room_id;
        $this->room_edit_name     = (string) $r->room_name;
        $this->room_edit_capacity = $r->capacity; // can be null or int
        $this->roomModal          = true;
        $this->resetErrorBag();
    }

    public function roomCloseEdit(): void
    {
        $this->roomModal = false;
        $this->reset('room_edit_id', 'room_edit_name', 'room_edit_capacity');
        $this->resetErrorBag();
    }

    public function roomUpdate(): void
    {
        $this->validate($this->roomEditRules());

        $r = Room::where('company_id', $this->companyId)->findOrFail($this->room_edit_id);
        $r->update([
            'room_name' => $this->room_edit_name,
            'capacity'  => $this->room_edit_capacity === '' ? null : (int) $this->room_edit_capacity, // <— coerce
        ]);

        $this->roomCloseEdit();
        session()->flash('success', 'Room berhasil diupdate.');
    }

    // Soft Delete
    public function roomDelete(int $id): void
    {
        $room = Room::where('company_id', $this->companyId)->findOrFail($id);
        $room->delete();

        if ($this->room_edit_id === $id) {
            $this->roomCloseEdit();
        }

        session()->flash('success', 'Room berhasil dipindahkan ke arsip (soft deleted).');
        $this->resetPage(pageName: 'roomsPage');
    }

    // Query
    public function getRoomsProperty()
    {
        return Room::query()
            ->where('company_id', $this->companyId)
            ->when($this->search !== '', fn($q) =>
                $q->where('room_name', 'like', "%{$this->search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'roomsPage'); // <— page name as 3rd arg
    }

    public function render()
    {
        return view('livewire.pages.superadmin.manageroom', [
            'rooms' => $this->rooms,
        ]);
    }
}
