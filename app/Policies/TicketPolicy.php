<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket)
    {
        // make a validation policy for ticket system with these rules:
        if ($user->is_agent === 'yes') {
            return true;
        }

        // allow ticket creator to view their own tickets
        if ($user->id === $ticket->user_id) {
            return true;
        }

        // allow assigned agent to view the ticket
        if ($user->id === $ticket->assigned_to_user_id) {
            return true;
        }

        return false;
    }

    // Ensure these are allowed for agents too if needed
    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Ticket $ticket)
    {
        if ($user->is_agent === 'yes') {
            return true;
        }
        return $user->id === $ticket->user_id;
    }
}