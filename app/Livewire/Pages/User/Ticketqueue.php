<?php
namespace App\Livewire\Pages\User;

use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketComment; 
use App\Models\TicketCommentRead;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
#[Title('Ticket Center')]
class Ticketqueue extends Component
{
    use WithPagination;

    public string $tab = 'queue';

    public string $search = '';
    public string $status = '';
    public string $priority = '';
    public string $claimPriority = '';

    // Simple documentation: Property to hold the total sum of unread comments for the navigation badge.
    public ?int $totalUnreadClaims = 0; 

    /**
     * Kanban columns for My Claims tab
     */
    public array $kanbanColumns = [
        'IN_PROGRESS' => 'In Progress',
        'RESOLVED'    => 'Resolved',
    ];

    protected $queryString = [
        'tab'           => ['except' => 'queue'],
        'search'        => ['except' => ''],
        'status'        => ['except' => ''],
        'priority'      => ['except' => ''],
        'claimPriority' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        // Simple documentation: Resets pagination when search input changes.
        $this->resetPage('qpage');
    }

    public function updatingStatus(): void
    {
        // Simple documentation: Resets pagination when status filter changes.
        $this->resetPage('qpage');
    }

    public function updatingPriority(): void
    {
        // Simple documentation: Resets pagination when priority filter changes.
        $this->resetPage('qpage');
    }

    public function updatedTab(): void
    {
        // Simple documentation: Resets pagination when switching tabs.
        $this->resetPage('qpage');
    }

    /**
     * Auto-close tickets:
     * All tickets with status RESOLVED for >= 1 day become CLOSED
     */
    protected function autoCloseResolvedTickets(): void
    {
        // Simple documentation: Finds and updates RESOLVED tickets older than one day to CLOSED.
        Ticket::where('status', 'RESOLVED')
            ->where('updated_at', '<=', now()->subDay())
            ->update([
                'status'     => 'CLOSED',
                'updated_at' => now(),
            ]);
    }

    /**
     * Builds the base query for the Ticket Queue tab. Unread comment count is
     * removed as these tickets are unclaimed.
     */
    protected function queueQuery()
    {
        // Simple documentation: Get base ticket query for unclaimed tickets.
        $user = auth()->user();

        return Ticket::with(['attachments', 'requester'])
            ->where('company_id', $user->company_id)
            ->where('department_id', $user->department_id)
            ->where('user_id', '!=', $user->user_id)
            ->where('status', '!=', 'RESOLVED')
            ->whereDoesntHave('assignments', function ($q) {
                // Simple documentation: Excludes tickets that have a current assignment (claimed tickets).
                $q->whereNull('deleted_at');
            })
            // Simple documentation: Unread comment count is not calculated for unclaimed tickets (Queue tab).
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('subject', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->priority !== '', fn ($q) => $q->where('priority', $this->priority))
            ->orderByDesc('created_at');
    }

    /**
     * Builds the base query for the My Claims tab, including unread comment count.
     */
    protected function claimsQuery()
    {
        // Simple documentation: Get claimed tickets with unread count for Claims tab.
        $user = auth()->user();
        $userId = Auth::id();

        // Subquery to find comment IDs that the current user has read
        $readCommentsQuery = TicketCommentRead::select('comment_id')
            ->where('user_id', $userId);

        // Subquery to calculate unread comment count per ticket
        $unreadCommentsSub = TicketComment::select('ticket_id', DB::raw('count(*) as unread_count'))
            // Exclude comments created by the current user (they are auto-read)
            ->where('user_id', '!=', $userId)
            // Count comments whose ID is NOT in the read list
            ->whereNotIn('comment_id', $readCommentsQuery) 
            ->groupBy('ticket_id');

        return TicketAssignment::query()
            ->with(['ticket.attachments', 'ticket.requester'])
            ->where('user_id', $user->user_id)
            ->whereNull('deleted_at')
            // Add the unread comment count via a join
            ->leftJoinSub($unreadCommentsSub, 'unread_comments', function ($join) {
                $join->on('ticket_assignments.ticket_id', '=', 'unread_comments.ticket_id');
            })
            // Select the count and the assignment columns
            ->select('ticket_assignments.*', 'unread_comments.unread_count')
            // closed tickets are not shown in My Claims
            ->whereRelation('ticket', 'status', '!=', 'CLOSED')
            ->when(
                $this->claimPriority !== '',
                fn ($q) => $q->whereHas(
                    'ticket',
                    fn ($qt) => $qt->where('priority', $this->claimPriority)
                )
            )
            ->orderByDesc('created_at');
    }

    public function claim(int $ticketId): void
    {
        // Simple documentation: Handles the claiming of an open ticket.
        $user = auth()->user();

        $alreadyClaimed = TicketAssignment::where('ticket_id', $ticketId)
            ->whereNull('deleted_at')
            ->exists();

        if ($alreadyClaimed) {
            $this->dispatch('notify', type: 'warning', message: 'Tiket sudah diklaim orang lain.');
            return;
        }

        DB::transaction(function () use ($ticketId, $user) {
            TicketAssignment::create([
                'ticket_id' => $ticketId,
                'user_id'   => $user->user_id,
            ]);

            Ticket::where('ticket_id', $ticketId)->update([
                'status'     => 'IN_PROGRESS',
                'updated_at' => now(),
            ]);
        });

        $this->resetPage('qpage');

        $this->dispatch('notify', type: 'success', message: 'Tiket berhasil di-claim.');
    }

    /**
     * Called by Kanban drag & drop on My Claims tab
     */
    public function moveClaim(int $ticketId, string $newStatus): void
    {
        // Simple documentation: Updates the status of a claimed ticket via Kanban drag-and-drop.
        $user = auth()->user();

        if (! in_array($newStatus, ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'], true)) {
            return;
        }

        $assignment = TicketAssignment::query()
            ->where('ticket_id', $ticketId)
            ->where('user_id', $user->user_id)
            ->whereNull('deleted_at')
            ->first();

        if (! $assignment) {
            $this->dispatch('notify', type: 'error', message: 'Tiket tidak ditemukan atau bukan milik Anda.');
            return;
        }

        DB::transaction(function () use ($ticketId, $newStatus) {
            Ticket::where('ticket_id', $ticketId)->update([
                'status'     => $newStatus,
                'updated_at' => now(),
            ]);
        });

        $this->dispatch('notify', type: 'success', message: 'Status tiket diperbarui.');
    }

    public function render()
    {
        // Simple documentation: The main render method that fetches data based on the active tab.
        $this->autoCloseResolvedTickets();

        // Simple documentation: Always fetch all claims data to ensure the navigation badge count is accurate, regardless of the active tab.
        $allClaims = $this->claimsQuery()->get();
        $this->totalUnreadClaims = $allClaims->sum('unread_count'); // Calculate count from the full collection

        $tickets = $this->tab === 'queue'
            ? $this->queueQuery()->paginate(10, ['*'], 'qpage')
            : collect();

        // Simple documentation: Only use the claims data for the view's claims section if the tab is active.
        $claims = $this->tab === 'claims'
            ? $allClaims
            : collect();

        return view('livewire.pages.user.ticketqueue', compact('tickets', 'claims'));
    }
}