<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchTicketController extends Controller
{
    public function __construct(private Ticket $model) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->model->newQuery()->where('code', $request->code)->get();
        if (empty($query)) {

            return response()->json(TicketResource::collection($query), Response::HTTP_OK);
        }
        return response()->json(['message' => 'La matricula no fue encontrada'], Response::HTTP_NOT_FOUND);
    }
}
