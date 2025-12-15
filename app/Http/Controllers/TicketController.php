<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Traits\TicketControllerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    use TicketControllerTrait;

    public function index(): JsonResponse
    {
        $tickets = $this->getTicketsPaginated();
        return response()->json($tickets);
    }
    public function store(CreateTicketRequest $request): JsonResponse
    {
        $ticket = $this->createTicket($request);
        return response()->json($ticket);
    }
    public function show(Ticket $ticket): JsonResponse
    {
        $ticket = $this->getTicket($ticket);
        return response()->json($ticket);
    }
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->updateTicket($request, $ticket);
        return response()->json($ticket);
    }
    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->deleteTicket($ticket);
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
