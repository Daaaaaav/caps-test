<?php
namespace App\Livewire\Components\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TicketComment;
use App\Models\TicketCommentRead;
use App\Models\Ticket; 
use App\Models\User; 

class Navbar extends Component
{
    // Total unread comments on tickets the user created (Status page scope for ALL users)
    public int $totalUnreadCount = 0; 
    
    // Total unread comments on Agent's claimed tickets (Queue/Claims scope)
    public int $unclaimedTicketCount = 0; 

    public $showDropdown = false;

    protected $listeners = [
        'commentAdded' => 'calculateCounts', 
        'ticketClaimed' => 'calculateCounts'
    ]; 

    public function mount()
    {
        $this->calculateCounts();
    }
    
    // A simple comment like an actual programmer's simple documentation
    // Calculates both user-created (Status) and agent-assigned (Queue) counts.
    public function calculateCounts(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            $this->totalUnreadCount = 0;
            $this->unclaimedTicketCount = 0;
            return;
        }

        // A simple comment like an actual programmer's simple documentation
        // Ensure we always use the primary key user_id for queries
        $userId = $user->user_id; 
        $isAgent = $user->is_agent === 'yes';

        // 1. Calculate the User's Status Count (Tickets *they created*)
        $this->calculateUserCreatedTicketCount($userId);
        
        // 2. Calculate the Agent's Queue Count (Tickets *claimed by them*)
        $this->calculateAssignedAgentTicketCount($userId, $isAgent);
    }

    /**
     * Calculates the total number of unread comments on tickets the current user created (Status).
     */
    private function calculateUserCreatedTicketCount(int $userId): void
    {
        // A simple comment like an actual programmer's simple documentation
        // Finds comments on tickets created by the user, that user hasn't read.
        $readCommentIds = TicketCommentRead::select('comment_id')->where('user_id', $userId);
        
        $this->totalUnreadCount = TicketComment::query()
            ->where('user_id', '!=', $userId)
            ->whereNotIn('comment_id', $readCommentIds)
            ->whereHas('ticket', function ($q) use ($userId) {
                $q->whereNotIn('status', ['CLOSED', 'closed', 'RESOLVED', 'resolved'])
                  ->where('user_id', $userId); // CRITICAL: Filter by ticket CREATOR
            })
            ->count();
    }
    
    /**
     * Calculates the total number of unread comments on tickets specifically assigned to the logged-in agent (Queue).
     */
    private function calculateAssignedAgentTicketCount(int $userId, bool $isAgent): void
    {
        if (!$isAgent) {
            $this->unclaimedTicketCount = 0;
            return;
        }

        // A simple comment like an actual programmer's simple documentation
        // Finds comments on tickets assigned to the agent, that agent hasn't read.
        $readCommentIds = TicketCommentRead::select('comment_id')->where('user_id', $userId);

        $this->unclaimedTicketCount = TicketComment::query()
            ->where('user_id', '!=', $userId) 
            ->whereNotIn('comment_id', $readCommentIds) 
            ->whereHas('ticket', function ($query) use ($userId) {
                $query->whereNotIn('status', ['CLOSED', 'closed', 'RESOLVED', 'resolved']);
                
                // CRITICAL FILTER: Must be explicitly assigned to this agent ID
                $query->whereHas('assignments', function ($qa) use ($userId) {
                    $qa->where('user_id', $userId)->whereNull('deleted_at');
                });
            })
            ->count();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.components.partials.navbar');
    }
}