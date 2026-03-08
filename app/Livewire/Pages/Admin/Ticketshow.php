<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Ticket as TicketModel;
use App\Models\User as UserModel;
use App\Models\TicketAssignment as TicketAssignmentModel;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\ValidationException;
use Throwable;

#[Layout('layouts.admin')]
#[Title('Admin - Ticket Detail')]
class Ticketshow extends Component
{
    public TicketModel $ticket;

    public string $status = 'open';
    public ?int $agent_id = null;

    public ?string $previewUrl = null; // modal image preview
    public string $newComment = '';

    private const ADMIN_ROLE_NAMES = ['Superadmin', 'Admin'];
    private const AGENT_ROLE_NAMES = ['User'];

    private const DB_TO_UI_STATUS_MAP = [
        'OPEN'        => 'open',
        'IN_PROGRESS' => 'in_progress',
        'RESOLVED'    => 'resolved',
        'CLOSED'      => 'closed',
        'DELETED'     => 'deleted',
    ];

    private const UI_TO_DB_STATUS_MAP = [
        'open'        => 'OPEN',
        'in_progress' => 'IN_PROGRESS',
        'resolved'    => 'RESOLVED',
        'closed'      => 'CLOSED',
        'deleted'     => 'DELETED',
    ];

    /**
     * Status yang membutuhkan assignment agent terlebih dahulu.
     */
    private const UI_STATUS_NEED_ASSIGNMENT = [
        'in_progress',
        'resolved',
        'closed',
    ];

    /** ------------------------------
     * Helpers
     * ------------------------------*/
    protected function currentAdmin()
    {
        $user = Auth::user();
        if ($user && !$user->relationLoaded('role')) {
            $user->load('role');
        }
        return $user;
    }

    protected function ensureAdmin(): bool
    {
        $u = $this->currentAdmin();
        return $u && $u->role && \in_array($u->role->name, self::ADMIN_ROLE_NAMES, true);
    }

    protected function isSuperadmin(): bool
    {
        $u = $this->currentAdmin();
        return $u && $u->role && $u->role->name === 'Superadmin';
    }

    protected function allowedAgentsQuery()
    {
        $admin = $this->currentAdmin();

        $q = UserModel::query()
            ->where('is_agent', 'yes') // only users marked as agent
            ->whereHas('role', fn($qr) => $qr->whereIn('name', self::AGENT_ROLE_NAMES));

        if (!$this->isSuperadmin()) {
            if (isset($admin->company_id)) {
                $q->where('company_id', $admin->company_id);
            }
            if (!empty($admin->department_id)) {
                $q->where('department_id', $admin->department_id);
            }
        }

        return $q;
    }

    /** Simple toast dispatcher (Livewire v3) */
    protected function toast(string $type, string $title, string $message, int $duration = 2500): void
    {
        // type: success | error | info | warning
        $this->dispatch('toast', type: $type, title: $title, message: $message, duration: $duration);
    }

    /** ------------------------------
     * Lifecycle
     * ------------------------------*/
    public function mount(TicketModel $ticket)
    {
        if (!$this->ensureAdmin()) {
            $this->toast('error', 'Unauthorized', 'Anda tidak memiliki hak akses.');
            abort(403);
        }

        $admin = $this->currentAdmin();
        if (!$this->isSuperadmin()) {
            if (isset($ticket->company_id) && isset($admin->company_id) && $ticket->company_id !== $admin->company_id) {
                $this->toast('error', 'Unauthorized', 'Ticket tidak dalam cakupan perusahaan Anda.');
                abort(403);
            }
            if (isset($ticket->department_id) && !empty($admin->department_id) && $ticket->department_id !== $admin->department_id) {
                $this->toast('error', 'Unauthorized', 'Ticket tidak dalam cakupan departemen Anda.');
                abort(403);
            }
        }

        $this->ticket = $ticket->load([
            'user:user_id,full_name,department_id',
            'user.department:department_id,department_name',
            'department:department_id,department_name',
            'assignment.agent:user_id,full_name',
            'attachments:attachment_id,ticket_id,file_url,file_type,original_filename,bytes',
            'comments' => fn($q) => $q->orderBy('created_at', 'asc'),
            'comments.user:user_id,full_name',
        ]);

        $this->status   = self::DB_TO_UI_STATUS_MAP[$ticket->status] ?? 'open';
        $this->agent_id = optional($ticket->assignment)->user_id;
    }

    /** ------------------------------
     * Actions
     * ------------------------------*/
    public function save()
    {
        if (!$this->ensureAdmin()) {
            $this->toast('error', 'Unauthorized', 'Anda tidak boleh mengubah ticket.');
            return;
        }

        try {
            $this->validate([
                'status'   => 'required|in:open,in_progress,resolved,closed',
                'agent_id' => 'nullable|integer',
            ]);

            $ticket = $this->ticket->fresh(['assignment']);

            if ($ticket->status === 'CLOSED') {
                $this->toast('warning', 'Terkunci', 'Ticket sudah CLOSED, tidak dapat diubah.');
                return;
            }

            // -------------------------------------------------------------
            // MODIFICATION START: Auto-set status to IN_PROGRESS upon assignment
            // -------------------------------------------------------------
            // Jika ada agent_id yang dipilih di UI, dan status ticket saat ini di DB adalah 'OPEN',
            // maka secara otomatis ubah status UI menjadi 'in_progress'.
            if (
                $this->agent_id // Agent is selected
                && $ticket->status === 'OPEN' // Ticket is currently OPEN in DB
                && $this->status === 'open' // UI selection is still 'open' (user didn't change it manually)
            ) {
                $this->status = 'in_progress';
                // Note: The UI status is now 'in_progress', which will be saved below.
            }
            // -------------------------------------------------------------
            // MODIFICATION END
            // -------------------------------------------------------------

            // Cek: status tertentu butuh assignment agent
            if (
                \in_array($this->status, self::UI_STATUS_NEED_ASSIGNMENT, true)
                && empty($this->agent_id)
            ) {
                $this->addError(
                    'status',
                    'Ticket harus di-assign ke agent sebelum status bisa diubah ke In Progress / Resolved / Closed.'
                );
                $this->toast('error', 'Validasi', 'Assign ticket ke agent terlebih dahulu.');
                return;
            }

            // Assignment
            $assignmentChanged = false;
            if ($this->agent_id) {
                $isAllowed = $this->allowedAgentsQuery()
                    ->where('user_id', $this->agent_id)
                    ->exists();
                if (!$isAllowed) {
                    $this->addError('agent_id', 'Agent belum di input.');
                    $this->toast('error', 'Validasi', 'Agent belum di input.');
                    return;
                }

                TicketAssignmentModel::updateOrCreate(
                    ['ticket_id' => $ticket->ticket_id],
                    ['user_id'   => $this->agent_id]
                );
                $assignmentChanged = true;
            } else {
                if ($ticket->assignment) {
                    $ticket->assignment()->delete();
                    $assignmentChanged = true;
                }
            }

            // Status
            $oldStatus      = $ticket->status;
            // $this->status now reflects 'in_progress' if auto-updated above.
            $ticket->status = self::UI_TO_DB_STATUS_MAP[$this->status] ?? 'OPEN';
            $statusChanged  = $oldStatus !== $ticket->status;

            $ticket->save();

            $this->ticket = $ticket->fresh([
                'user',
                'assignment.agent',
                'attachments',
                'comments' => fn($q) => $q->orderBy('created_at', 'asc'),
                'comments.user:user_id,full_name',
            ]);

            // Toaster ringkas per perubahan dan reload halaman jika ada perubahan
            if ($assignmentChanged || $statusChanged) {
                if ($assignmentChanged && $statusChanged) {
                    $this->toast('success', 'Tersimpan', "Assignment & status Ticket #{$ticket->ticket_id} diperbarui.");
                } elseif ($assignmentChanged) {
                    $this->toast('success', 'Tersimpan', "Assignment Ticket #{$ticket->ticket_id} diperbarui.");
                } elseif ($statusChanged) {
                    $this->toast('success', 'Tersimpan', "Status Ticket #{$ticket->ticket_id} diperbarui.");
                }
                
                // Reload the page after successful save
                $this->js('window.location.reload()');
            } else {
                $this->toast('info', 'Tidak Ada Perubahan', 'Tidak ada perubahan yang disimpan.');
            }
        } catch (ValidationException $e) {
            $first = collect($e->validator->errors()->all())->first() ?? 'Periksa input Anda.';
            $this->toast('error', 'Validasi', $first, 3000);
            throw $e;
        } catch (Throwable $e) {
            $this->toast('error', 'Gagal', 'Terjadi kesalahan saat menyimpan perubahan.', 3000);
            // optional: log($e)
        }
    }

    public function openPreview(string $url)
    {
        $this->previewUrl = $url;
        $this->toast('info', 'Preview', 'Membuka pratinjau lampiran.', 1500);
    }

    public function closePreview()
    {
        $this->previewUrl = null;
        $this->toast('info', 'Preview', 'Menutup pratinjau lampiran.', 1200);
    }

    public function addComment()
    {
        if (!$this->ensureAdmin()) {
            $this->toast('error', 'Unauthorized', 'Anda tidak boleh berkomentar.', 3000);
            return;
        }

        if ($this->ticket->status === 'CLOSED') {
            $this->toast('error', 'Ditolak', 'Ticket sudah CLOSED.', 3000);
            return;
        }

        try {
            $this->validate([
                'newComment' => ['required', 'string', 'min:2'],
            ], [
                'newComment.required' => 'Komentar tidak boleh kosong.',
                'newComment.min'      => 'Komentar terlalu pendek.',
            ]);

            $this->ticket->comments()->create([
                'user_id'      => Auth::id(),
                'comment_text' => $this->newComment,
            ]);

            $this->reset('newComment');

            $this->ticket->load([
                'comments' => fn($q) => $q->orderBy('created_at', 'asc'),
                'comments.user:user_id,full_name',
            ]);

            $this->toast('success', 'Berhasil', 'Komentar ditambahkan.', 2500);
        } catch (ValidationException $e) {
            $first = collect($e->validator->errors()->all())->first() ?? 'Periksa input Anda.';
            $this->toast('error', 'Validasi', $first, 3000);
            throw $e;
        } catch (Throwable $e) {
            $this->toast('error', 'Gagal', 'Terjadi kesalahan saat menambah komentar.', 3000);
            // optional: log($e)
        }
    }

    /** ------------------------------
     * Render
     * ------------------------------*/
    public function render()
    {
        $agents = $this->allowedAgentsQuery()->orderBy('full_name')->get(['user_id', 'full_name']);

        return view('livewire.pages.admin.ticketshow', [
            'agents' => $agents,
            't'      => $this->ticket,
        ]);
    }
}