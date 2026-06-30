<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Guestbook as GuestbookModel;
#[Layout('layouts.receptionist')]
#[Title('Guestbook Status')]
class GuestbookStatus extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filters
    public string $q = '';
    public string $activeTab = 'pending'; // pending | ongoing
    public int $perPage = 9;

    // Officer filter (sidebar)
    public ?string $petugasFilter = null;

    // Edit modal
    public bool $showEdit = false;
    public ?int $editId = null;
    public array $edit = [
        'name'            => null,
        'email'           => null,
        'phone_number'    => null,
        'instansi'        => null,
        'keperluan'       => null,
        'petugas_penjaga' => null,
    ];

    protected function rulesEdit(): array
    {
        return [
            'edit.name'            => ['required', 'string', 'max:255'],
            'edit.email'           => ['nullable', 'email', 'max:255'],
            'edit.phone_number'    => ['nullable', 'string', 'max:50'],
            'edit.instansi'        => ['nullable', 'string', 'max:255'],
            'edit.keperluan'       => ['nullable', 'string', 'max:255'],
            'edit.petugas_penjaga' => ['required', 'string', 'max:255'],
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function companyId(): ?int
    {
        return Auth::user()?->company_id;
    }

    private function findOwnedOrFail(int $id): GuestbookModel
    {
        return GuestbookModel::whereKey($id)
            ->where('company_id', $this->companyId())
            ->firstOrFail();
    }

    // -----------------------------------------------------------------------
    // Filter reactivity
    // -----------------------------------------------------------------------

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        if (!in_array($tab, ['pending', 'ongoing'], true)) {
            return;
        }
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function clearPetugasFilter(): void
    {
        $this->petugasFilter = null;
        $this->resetPage();
    }

    // -----------------------------------------------------------------------
    // Computed properties
    // -----------------------------------------------------------------------

    /**
     * Entries where QR has been sent but not yet scanned (status = pending).
     * Also catches legacy rows with no qr_token that still have no jam_out.
     */
    public function getPendingEntriesProperty()
    {
        $q = GuestbookModel::query()
            ->where('company_id', $this->companyId())
            ->whereNull('jam_out')
            ->whereNull('deleted_at')
            ->where(function ($sub) {
                $sub->where('qr_status', 'pending')
                    ->orWhereNull('qr_status');
            });

        if ($this->petugasFilter) {
            $q->where('petugas_penjaga', $this->petugasFilter);
        }

        if ($this->q !== '') {
            $term = '%' . $this->q . '%';
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('instansi', 'like', $term)
                    ->orWhere('keperluan', 'like', $term)
                    ->orWhere('petugas_penjaga', 'like', $term);
            });
        }

        return $q->orderByDesc('created_at')
                 ->paginate($this->perPage, ['*'], 'pendingPage');
    }

    /**
     * Entries where at least one scan has happened (status = ongoing).
     * Guest is confirmed onsite, no jam_out yet.
     */
    public function getOngoingEntriesProperty()
    {
        $q = GuestbookModel::query()
            ->where('company_id', $this->companyId())
            ->whereNull('jam_out')
            ->whereNull('deleted_at')
            ->where('qr_status', 'ongoing');

        if ($this->petugasFilter) {
            $q->where('petugas_penjaga', $this->petugasFilter);
        }

        if ($this->q !== '') {
            $term = '%' . $this->q . '%';
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                    ->orWhere('instansi', 'like', $term)
                    ->orWhere('keperluan', 'like', $term)
                    ->orWhere('petugas_penjaga', 'like', $term);
            });
        }

        return $q->orderByDesc('created_at')
                 ->paginate($this->perPage, ['*'], 'ongoingPage');
    }

    // -----------------------------------------------------------------------
    // Counts for tab badges (unpaginated, fast)
    // -----------------------------------------------------------------------

    public function getPendingCountProperty(): int
    {
        return GuestbookModel::where('company_id', $this->companyId())
            ->whereNull('jam_out')
            ->whereNull('deleted_at')
            ->where(function ($sub) {
                $sub->where('qr_status', 'pending')
                    ->orWhereNull('qr_status');
            })
            ->count();
    }

    public function getOngoingCountProperty(): int
    {
        return GuestbookModel::where('company_id', $this->companyId())
            ->whereNull('jam_out')
            ->whereNull('deleted_at')
            ->where('qr_status', 'ongoing')
            ->count();
    }

    // -----------------------------------------------------------------------
    // Actions
    // -----------------------------------------------------------------------

    /** Open edit modal */
    public function openEdit(int $id): void
    {
        $row = $this->findOwnedOrFail($id);

        $this->editId = $row->guestbook_id;
        $this->edit = [
            'name'            => $row->name,
            'email'           => $row->email,
            'phone_number'    => $row->phone_number,
            'instansi'        => $row->instansi,
            'keperluan'       => $row->keperluan,
            'petugas_penjaga' => $row->petugas_penjaga,
        ];
        $this->resetValidation();
        $this->showEdit = true;
    }

    public function saveEdit(): void
    {
        $this->validate($this->rulesEdit());

        $row = $this->findOwnedOrFail($this->editId);
        $row->update([
            'name'            => $this->edit['name'],
            'email'           => $this->edit['email'] ?: null,
            'phone_number'    => $this->edit['phone_number'],
            'instansi'        => $this->edit['instansi'],
            'keperluan'       => $this->edit['keperluan'],
            'petugas_penjaga' => $this->edit['petugas_penjaga'],
        ]);

        $this->showEdit = false;
        $this->dispatch('toast', type: 'success', title: __('app.toast_updated_title'), message: __('app.toast_updated_message'), duration: 3000);
        $this->dispatch('$refresh');
    }

    /** Manually check out a visitor right now */
    public function checkOutNow(int $id): void
    {
        $row = $this->findOwnedOrFail($id);
        $row->update([
            'jam_out'    => Carbon::now()->format('H:i'),
            'qr_status'  => 'completed',
        ]);

        $this->dispatch('toast', type: 'success', title: __('app.toast_checkout_title'), message: __('app.toast_checkout_message'), duration: 3000);
        $this->dispatch('$refresh');
    }

    /** Resend QR email for a pending entry */
    public function resendQr(int $id): void
    {
        $row = $this->findOwnedOrFail($id);

        if (!$row->email || !$row->qr_token) {
            $this->dispatch('toast', type: 'warning', title: 'Tidak dapat dikirim', message: 'Entri ini tidak memiliki email atau token QR.', duration: 4000);
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($row->email)
                ->send(new \App\Mail\GuestbookQrMail($row));

            $this->dispatch('toast', type: 'success', title: 'QR Dikirim Ulang', message: 'QR code berhasil dikirim ulang ke ' . $row->email . '.', duration: 4000);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('GuestbookQrMail resend failed: ' . $e->getMessage(), [
                'exception' => $e,
                'guestbook_id' => $id,
            ]);
            $this->dispatch('toast', type: 'error', title: 'Gagal Kirim [DEBUG]', message: get_class($e) . ': ' . $e->getMessage(), duration: 15000);
        }
    }

    public function render()
    {
        return view('livewire.pages.receptionist.guestbook-status', [
            'pendingEntries' => $this->pendingEntries,
            'ongoingEntries' => $this->ongoingEntries,
            'pendingCount'   => $this->pendingCount,
            'ongoingCount'   => $this->ongoingCount,
        ]);
    }
}
