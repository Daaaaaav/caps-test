<?php
namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketAssignment;
use App\Models\TicketCommentRead; 
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Models\User;

#[Layout('layouts.app')]
#[Title('Ticket Detail')]
class Ticketshow extends Component
{
    public Ticket $ticket;
    public string $newComment = '';

    public bool $canEditStatus = false;
    public string $statusEdit = '';
    protected array $allowedStatuses = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];
    
    // Flag to control comment box visibility and submission permission
    public bool $canComment = false; 
    // Flag to control comment visibility based on admin/requester/assigned status
    public bool $canViewComments = false; 

    public function mount(Ticket $ticket): void
    {
        $this->ensureAccess($ticket);

        // Determine if the user is an assigned agent, admin, or the requester
        $this->canViewComments = $this->checkCommentViewPermission($ticket);
        
        // Use the $this->canViewComments flag to conditionally load comments
        $this->ticket = $ticket->load([
            'department:department_id,department_name',
            'requesterDepartment:department_id,department_name',
            'user:user_id,full_name',
            'attachments',
            // Load comments ONLY if the user has permission to view them
            'comments' => fn($q) => $q->when($this->canViewComments, function ($query) {
                // If the user CAN view comments, load them all
                return $query->orderBy('created_at', 'asc')->with([ 
                    'user:user_id,full_name',
                    'reads' => fn($qr) => $qr->where('user_id', Auth::id()),
                ]);
            }, function ($query) {
                // If the user CANNOT view comments, load an empty set
                return $query->whereRaw('1 = 0'); 
            }),
            'assignments' => fn($q) => $q->whereNull('deleted_at')->with([
                'user:user_id,full_name'
            ]),
        ]);

        $this->canEditStatus = $this->isAssignedAgent($this->ticket->ticket_id, Auth::user()->user_id);
        $this->statusEdit    = $this->ticket->status;
        
        // Calculate comment post permission
        $this->canComment = $this->checkCommentPermission();
        
        // Mark comments as read after mounting, but only if they were loaded/viewable
        if ($this->canViewComments) {
            $this->markCommentsAsRead();
        }
    }

    /**
     * Ensure the authenticated user has general access to view the ticket.
     */
    protected function ensureAccess(Ticket $ticket): void
    {
        // This will automatically check app/Policies/TicketPolicy.php
        $this->authorize('view', $ticket);
    }

    /**
     * Check if the authenticated user is currently assigned to this ticket.
     */
    protected function isAssignedAgent(int $ticketId, int $userId): bool
    {
        return TicketAssignment::where('ticket_id', $ticketId)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->exists();
    }
    
    /**
     * Determine if the current authenticated user can view existing comments.
     * Permissions: Admin/Superadmin OR Ticket Creator OR Assigned Agent.
     */
    protected function checkCommentViewPermission(Ticket $ticket): bool
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->user_id;
        
        // Get the role name, assuming a 'role' relationship exists on the User model
        $roleName = $user->role->name ?? '';
        
        // Check 1: Superadmins and Admins can always view (using role name)
        if (in_array($roleName, ['Superadmin', 'Admin'], true)) {
            return true;
        }

        // Check 2: The ticket creator (Requester) can always view
        if ($userId === $ticket->user_id) {
            return true;
        }

        // Check 3: Any other user (Agent/User/Receptionist) can ONLY view if they are an Assigned Agent.
        $isAssigned = $this->isAssignedAgent($ticket->ticket_id, $userId);

        return $isAssigned;
    }

    /**
     * Determine if the current authenticated user can add a comment.
     * Permissions: Admin/Superadmin OR Ticket Creator OR Assigned Agent.
     */
    protected function checkCommentPermission(): bool
    {
        // Check 1: Cannot comment if the ticket is CLOSED (resolved/closed/complete)
        $status = strtolower($this->ticket->status ?? 'open');
        $isClosed = in_array($status, ['resolved', 'closed', 'complete'], true);
        if ($isClosed) {
            return false;
        }
        
        // Must be able to view comments to post one (ensures consistency)
        if (!$this->canViewComments) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();
        $userId = $user->user_id;
        
        // Get the role name
        $roleName = $user->role->name ?? '';
        
        // Check 2: Superadmins and Admins can always comment (using role name)
        if (in_array($roleName, ['Superadmin', 'Admin'], true)) {
            return true;
        }

        // Check 3: The ticket creator (Requester) can comment
        if ($userId === $this->ticket->user_id) {
            return true;
        }

        // Check 4: Any other user (Agent/User/Receptionist) can ONLY comment if they are an Assigned Agent.
        return $this->isAssignedAgent($this->ticket->ticket_id, $userId);
    }

    /**
     * Marks all visible comments for this ticket as read by the current user.
     */
    protected function markCommentsAsRead(): void
    {
        $userId = Auth::id();
        // The comments collection is already filtered to only contain viewable comments
        $commentIds = $this->ticket->comments 
            ->where('user_id', '!=', $userId) // We only care about comments made by others
            ->pluck('comment_id');

        if ($commentIds->isEmpty()) {
            return;
        }
        
        // Find comments that the current user hasn't read yet
        $alreadyReadCommentIds = TicketCommentRead::where('user_id', $userId)
            ->whereIn('comment_id', $commentIds)
            ->pluck('comment_id');

        $commentsToMarkAsRead = $commentIds->diff($alreadyReadCommentIds);

        if ($commentsToMarkAsRead->isNotEmpty()) {
            // Bulk insert the new 'read' records
            $data = $commentsToMarkAsRead->map(fn($id) => [
                'comment_id' => $id,
                'user_id'    => $userId,
                'read_at'    => now(),
            ])->all();

            // Insert into the ticket_comment_reads table
            TicketCommentRead::insert($data);
        }
    }

    /**
     * Handle the submission for updating the ticket status.
     */
    public function updateStatus(): void
    {
        if (! $this->canEditStatus) {
            abort(403);
        }

        $this->validate([
            'statusEdit' => ['required', 'string', 'in:OPEN,IN_PROGRESS,RESOLVED,CLOSED'],
        ]);

        try {
            $this->ticket->update([
                'status'     => $this->statusEdit,
                'updated_at' => now(),
            ]);

            // Reload logic must use the $this->canViewComments flag for consistency
            $this->ticket->refresh()->load([
                // Reload comments with read status
                'comments' => fn($q) => $q->when($this->canViewComments, function ($query) {
                    return $query->orderBy('created_at', 'asc')->with([
                        'user:user_id,full_name',
                        'reads' => fn($qr) => $qr->where('user_id', Auth::id()),
                    ]);
                }, function ($query) {
                    return $query->whereRaw('1 = 0');
                }),
                'assignments' => fn($q) => $q->whereNull('deleted_at')->with([
                    'user:user_id,full_name'
                ]),
            ]);
            
            // Recalculate comment permission in case the status change affects it (e.g., closing the ticket)
            $this->canComment = $this->checkCommentPermission();

            $this->dispatch('toast', type: 'success', title: 'Updated', message: 'Status updated.', duration: 2500);

        } catch (Throwable $e) {
            // Dispatch error details to browser console
            $this->dispatch('consoleLogEvent', message: 'Ticket Status Update Failure', error: $e->getMessage(), file: $e->getFile(), line: $e->getLine());
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Terjadi kesalahan saat memperbarui status.', duration: 3000);
        }
    }

    /**
     * Handle the submission for adding a new comment.
     */
    public function addComment(): void
    {
        if (! $this->canComment) {
            abort(403, 'Unauthorized to post a comment on this ticket.');
        }

        // --- Step 1: Validation ---
        try {
            $this->validate([
                'newComment' => ['required', 'string', 'min:3'],
            ]);
        } catch (ValidationException $e) {
            $first = collect($e->validator->errors()->all())->first() ?? 'Periksa kembali input Anda.';
            $this->dispatch('toast', type: 'error', title: 'Validasi Gagal', message: $first, duration: 3000);
            throw $e; 
        } 

        // --- Step 2: Database Operations, Reset, Reload, and Success Toast ---
        try {
            // Create the new comment. 
            $newComment = TicketComment::create([
                'ticket_id'    => $this->ticket->ticket_id,
                'user_id'      => Auth::id(),
                'comment_text' => $this->newComment,
            ]);
            
            // CRITICAL CHECK: Ensure the model has an ID before proceeding.
            if (!$newComment || !$newComment->comment_id) {
                // Throw an error that is easier to trace if the model creation failed silently.
                throw new \Exception("TicketComment failed to create or retrieve ID.");
            }

            // Mark the comment as read by the creator
            TicketCommentRead::create([
                'comment_id' => $newComment->comment_id, // <-- FIX: Use the guaranteed ID
                'user_id'    => Auth::id(),
                'read_at'    => now(),
            ]);

            $this->reset('newComment');

            // Reload the ticket to include the new comment, using the canViewComments flag
            $this->ticket->load([
                'attachments', 
                'comments' => fn($q) => $q->when($this->canViewComments, function ($query) {
                    return $query->orderBy('created_at', 'asc')->with([
                        'user:user_id,full_name',
                        'reads' => fn($qr) => $qr->where('user_id', Auth::id()),
                    ]);
                }, function ($query) {
                    return $query->whereRaw('1 = 0');
                }),
            ]);

            // Success! The entire transaction completed without a hitch.
            $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Komentar ditambahkan.', duration: 3000);

        } catch (Throwable $e) {
            // Dispatch error details to browser console for debugging
            $this->dispatch('consoleLogEvent', message: 'Ticket Comment Failure (Final Attempt)', error: $e->getMessage(), file: $e->getFile(), line: $e->getLine());
            $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Terjadi kesalahan saat menambah komentar.', duration: 3000);
        }
    }

    public function render()
    {
        return view('livewire.pages.user.ticketshow', [
            'allowedStatuses' => $this->allowedStatuses,
        ]);
    }
} // Livewire component for ticket detail view