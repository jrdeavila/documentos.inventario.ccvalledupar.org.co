<?php

namespace App\Traits;

use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketCollection;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

trait TicketControllerTrait
{
    private function getTicketsPaginated(): TicketCollection
    {
        $tickets = Ticket::paginate(10);
        return new TicketCollection($tickets);
    }

    private function getTicket(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket);
    }

    private function createTicket(CreateTicketRequest $request): TicketResource
    {
        try {
            DB::beginTransaction();
            $ticket = $request->createTicket();
            DB::commit();
            return new TicketResource($ticket);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateTicket(UpdateTicketRequest $request, Ticket $ticket): TicketResource
    {
        try {
            DB::beginTransaction();
            $ticket = $request->updateTicket($ticket);
            DB::commit();
            return new TicketResource($ticket);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function deleteTicket(Ticket $ticket): void
    {
        try {
            DB::beginTransaction();
            $ticket->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
