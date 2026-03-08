<?php

namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment; // Pastikan model ini ada atau gunakan DB facade
use Illuminate\Validation\ValidationException;
use Throwable;

#[Layout('layouts.app')]
#[Title('Create Ticket')]
class CreateTicket extends Component
{
    // form
    public string $subject = '';
    public string $priority = 'low';
    public ?int $assigned_department_id = null;
    public string $description = '';

    // display
    public string $requester_department = '-';
    public array $departments = [];

    // temp items json (dari JS upload temp)
    public string $temp_items_json = '[]';

    // per-file & total limits (MB)
    public int $per_file_max_mb = 10;
    public int $total_quota_mb = 15;

    public function mount(): void
    {
        $user = Auth::user()->loadMissing(['department', 'company']);

        $this->requester_department = optional($user->department)->department_name ?? '-';
        $this->assigned_department_id = $user->department_id;

        $this->departments = Department::query()
            ->when($user->company_id, fn($q) => $q->where('company_id', $user->company_id))
            ->orderBy('department_name', 'asc')
            ->get(['department_id', 'department_name'])
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high'],
            'assigned_department_id' => ['required', 'exists:departments,department_id'],
            'description' => ['nullable', 'string', 'max:10000'],
            'temp_items_json' => ['nullable', 'string'],
        ];
    }

    public function save()
    {
        try {
            $this->validate();

            $user = Auth::user()->loadMissing(['department', 'company']);

            // 1. Create Ticket
            $ticket = Ticket::create([
                'company_id' => $user->company_id,
                'requestdept_id' => $user->department_id,
                'department_id' => $this->assigned_department_id,
                'user_id' => $user->getKey(),
                'subject' => $this->subject,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => 'OPEN',
            ]);

            // 2. Parse JSON lampiran
            $items = [];
            try {
                $items = json_decode($this->temp_items_json ?? '[]', true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($items)) $items = [];
            } catch (\JsonException $je) {
                $items = [];
            }

            // 3. Cek total kuota size (Server Side check)
            $incomingBytes = array_sum(array_map(fn($it) => (int) ($it['bytes'] ?? 0), $items));
            $quotaBytes = (int) $this->total_quota_mb * 1024 * 1024;

            if ($incomingBytes > $quotaBytes) {
                $ticket->delete(); // Rollback
                $this->addError('attachments', "Total attachment size exceeds {$this->total_quota_mb} MB.");
                return;
            }

            // 4. Finalisasi Lampiran (Pindah dari Temp ke Folder Tiket)
            if (!empty($items)) {
                try {
                    $req = new \Illuminate\Http\Request([
                        'ticket_id' => $ticket->getKey(),
                        'items' => $items,
                    ]);
                    
                    // Panggil controller untuk memindahkan file fisik dan insert ke DB
                    app(\App\Http\Controllers\AttachmentController::class)->finalizeTemp($req);
                    
                } catch (Throwable $e) {
                    Log::warning('Attachment finalizeTemp failed', [
                        'ticket_id' => $ticket->getKey(),
                        'err' => $e->getMessage(),
                    ]);
                    $this->dispatch('toast', ['type' => 'warning', 'message' => 'Tiket dibuat, tetapi beberapa lampiran gagal diproses.']);
                }
            }

            // 5. Reset & Redirect
            $this->reset(['subject', 'priority', 'assigned_department_id', 'description', 'temp_items_json']);
            $this->priority = 'low';
            $this->assigned_department_id = $user->department_id;
            $this->temp_items_json = '[]';

            session()->flash('success', 'Tiket berhasil dibuat.');
            return redirect()->route('ticketstatus');

        } catch (ValidationException $e) {
            $first = collect($e->validator->errors()->all())->first() ?? 'Periksa kembali input Anda.';
            $this->dispatch('toast', ['type' => 'error', 'message' => $first]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('CreateTicket.save failed', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = app()->environment('local') ? $e->getMessage() : 'Terjadi kesalahan tak terduga.';
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            return;
        }
    }

    public function render()
    {
        return view('livewire.pages.user.createticket');
    }
} 