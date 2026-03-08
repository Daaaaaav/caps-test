<?php

namespace App\Livewire\Tickets;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Ticket;
use App\Models\Department;
use App\Models\User;

class QuickTicketModal extends Component
{
    public bool $show = false;

    // form fields
    public string $subject = '';
    public string $priority = 'low'; // default lowercase
    public ?int $department_id = null;      // departemen tujuan
    public string $description = '';

    // dropdown data
    public array $departments = [];

    public function mount(): void
    {
        $user = Auth::user();

        // isi default assign-to = dept user sendiri
        $this->department_id = $user->department_id;

        // ambil semua departemen (filter by company kalau perlu)
        $this->departments = Department::query()
            ->when($user->company_id, fn ($q) => $q->where('company_id', $user->company_id))
            ->orderBy('department_name', 'asc')
            ->get(['department_id', 'department_name'])
            ->map(fn($d) => [
                'id'   => $d->department_id,
                'name' => $d->department_name,
            ])->values()->all();
    }

    #[On('open-quick-ticket')]
    public function open(): void
    {
        $this->resetForm();
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function submit(): void
    {
        $this->validate([
            'subject'       => ['required','string','min:3','max:200'],
            'priority'      => ['required', Rule::in(['low','medium','high'])],
            'department_id' => ['required','integer','exists:departments,department_id'],
            'description'   => ['required','string','min:5'],
        ]);

        $user = Auth::user()->loadMissing(['department','company']);

        Ticket::create([
            'company_id'     => $user->company_id,
            'requestdept_id' => $user->department_id,  // dept si pembuat
            'department_id'  => $this->department_id,  // dept tujuan
            'user_id'        => $user->user_id,        // pk users
            'subject'        => $this->subject,
            'priority'       => $this->priority,
            'status'         => 'OPEN',
            'description'    => $this->description,
        ]);

        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => 'Ticket berhasil dibuat.'
        ]);

        $this->close();
    }

    protected function resetForm(): void
    {
        $this->subject = '';
        $this->priority = 'low';
        $this->department_id = null;
        $this->description = '';
    }

    public function render()
    {
        return view('livewire.tickets.quick-ticket-modal');
    }
}
