<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tickets.viewAny', 'web');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.view', 'web') && $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tickets.create', 'web');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.update', 'web') && $ticket->user_id === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.delete', 'web') && $ticket->user_id === $user->id;
    }
}
