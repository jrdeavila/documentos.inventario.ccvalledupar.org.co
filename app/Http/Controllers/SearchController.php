<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\MregEstInscritoResource;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): JsonResponse
    {
        $results = $request->search();

        return response()->json(MregEstInscritoResource::collection($results));
    }
}
