<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Support\Audit;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        Audit::log([
            'action' => 'created',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'new_values' => $ticket->getAttributes(),
        ]);
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        Audit::log([
            'action' => 'update',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
            'old_values' => $ticket->getOriginal(),
            'new_values' => $ticket->getChanges(),
        ]);
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        Audit::log([
            'action' => 'deleted',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'old_values' => $ticket->getAttributes(),
        ]);
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
