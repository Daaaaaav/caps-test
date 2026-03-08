<?php
namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- NEW IMPORT
use App\Models\Ticket;
use App\Models\Department;
use App\Models\TicketAssignment;
use App\Models\TicketCommentRead; // <-- NEW IMPORT

#[Layout('layouts.app')]
#[Title('Ticket Status')]
class Ticketstatus extends Component
{
    public string $statusFilter = '';
    public string $priorityFilter = '';
    public string $departmentFilter = '';
    public string $sortFilter = 'recent';
    public $departments;

    private const DB_ALLOWED_STATUSES = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];

    private const UI_TO_DB_STATUS_MAP = [
        'open'        => 'OPEN',
        'in_progress' => 'IN_PROGRESS',
        'resolved'    => 'RESOLVED',
        'closed'      => 'CLOSED',
        'OPEN'        => 'OPEN',
        'IN_PROGRESS' => 'IN_PROGRESS',
        'RESOLVED'    => 'RESOLVED',
        'CLOSED'      => 'CLOSED',
        'assigned'    => 'ASSIGNED',
    ];

    private const UI_TO_DB_PRIORITY_MAP = [
        'low'    => 'low',
        'medium' => 'medium',
        'high'   => 'high',
        'LOW'    => 'low',
        'MEDIUM' => 'medium',
        'HIGH'   => 'high',
    ];

    protected $queryString = [
        'statusFilter'     => ['except' => ''],
        'priorityFilter'   => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'sortFilter'       => ['except' => 'recent'],
    ];

    public function mount(): void
    {
        $user = Auth::user();

        $this->departments = Department::query()
            ->when($user->company_id, fn($q) => $q->where('company_id', $user->company_id))
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);
    }

    private function baseQuery()
    {
        $user = Auth::user();
        $userId = $user->getKey();
        
        // Subquery to find comment IDs that the current user has read
        $readCommentsQuery = TicketCommentRead::select('comment_id')
            ->where('user_id', $userId);

        return Ticket::query()
            ->with([
                'department:department_id,department_name',
                'user:user_id,full_name',
                'assignments' => fn($q) => $q->whereNull('deleted_at')->with([
                    'user:user_id,full_name'
                ]),
            ])
            ->withCount([
                'assignments as agent_count' => fn($q) => $q->whereNull('deleted_at'),
                // Add column for UNREAD comments count
                'comments as unread_comments_count' => function ($query) use ($userId, $readCommentsQuery) {
                    $query->select(DB::raw('count(ticket_comments.comment_id)'))
                          // Exclude comments created by the current user (they are auto-read)
                          ->where('user_id', '!=', $userId) 
                          // Count comments whose ID is NOT in the read list
                          ->whereNotIn('ticket_comments.comment_id', $readCommentsQuery);
                }
            ])
            ->where('user_id', $userId);
    }

    public function render()
    {
        $q = $this->baseQuery();

        if ($this->statusFilter !== '') {
            $mapped = self::UI_TO_DB_STATUS_MAP[$this->statusFilter] ?? '';
            if ($mapped === 'ASSIGNED') {
                $q->whereIn('ticket_id', TicketAssignment::query()
                    ->whereNull('deleted_at')
                    ->select('ticket_id'));
            } elseif ($mapped && \in_array($mapped, self::DB_ALLOWED_STATUSES, true)) {
                $q->where('status', $mapped);
            } else {
                $q->whereRaw('1=0');
            }
        }

        if ($this->priorityFilter !== '') {
            $prio = self::UI_TO_DB_PRIORITY_MAP[$this->priorityFilter] ?? '';
            if ($prio) {
                $q->where('priority', $prio);
            } else {
                $q->whereRaw('1=0');
            }
        }

        if ($this->departmentFilter !== '') {
            $q->where('department_id', (int) $this->departmentFilter);
        }

        match ($this->sortFilter) {
            'oldest' => $q->orderBy('created_at', 'asc'),
            'due'    => $q->orderBy('due_at', 'asc')->orderBy('updated_at', 'asc'),
            default  => $q->orderBy('created_at', 'desc'),
        };

        $tickets = $q->paginate(10); // Use paginate if ticket list is long.

        return view('livewire.pages.user.ticketstatus', [
            'tickets'     => $tickets,
            'departments' => $this->departments,
        ]);
    }

    public function markComplete(int $ticketId): void
    {
        $ticket = Ticket::whereKey($ticketId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$ticket) {
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Ticket tidak ditemukan.', duration: 3000);
            return;
        }

        $hasAgent = TicketAssignment::query()
            ->where('ticket_id', $ticket->ticket_id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$hasAgent && $ticket->status !== 'RESOLVED') {
            $this->dispatch('toast', type: 'warning', title: 'Tidak bisa', message: 'Ticket belum memiliki agent.', duration: 3500);
            return;
        }

        switch ($ticket->status) {
            case 'OPEN':
            case 'IN_PROGRESS':
                $ticket->update(['status' => 'RESOLVED']);
                $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Ticket ditandai Resolved.', duration: 3000);
                break;

            case 'RESOLVED':
                $ticket->update(['status' => 'CLOSED']);
                $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Ticket ditutup.', duration: 3000);
                break;

            case 'CLOSED':
                $this->dispatch('toast', type: 'info', title: 'Info', message: 'Ticket sudah Closed.', duration: 2500);
                break;
        }
    }
}