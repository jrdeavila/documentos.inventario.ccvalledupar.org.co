<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateManyTicketRequest;
use App\Http\Resources\TicketResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CreateManyTicketController extends Controller
{
    public function __invoke(CreateManyTicketRequest $request): JsonResponse
    {
        return response()->json(
            TicketResource::collection($request->createManyTickets($request)),
            Response::HTTP_CREATED
        );
    }
}
