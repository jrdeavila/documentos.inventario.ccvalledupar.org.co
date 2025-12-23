<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchTicketController extends Controller
{
    public function __construct(private Ticket $model) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Validación de entrada
        $validated = $request->validate([
            'code'       => ['nullable', 'string', 'max:100'],
            'query_type' => ['nullable', 'string', 'max:50'],
            'status'     => ['nullable', 'string', 'max:50'],
            'user_id'    => ['nullable', 'integer'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'       => ['nullable', 'integer', 'min:1'],
            'sort_by'    => ['nullable', 'in:id,code,status,created_at'],
            'sort_dir'   => ['nullable', 'in:asc,desc'],
        ]);

        // Valores por defecto de paginación y orden
        $perPage = $validated['per_page'] ?? 20;
        $sortBy  = $validated['sort_by'] ?? 'id';
        $sortDir = $validated['sort_dir'] ?? 'desc';

        $query = $this->model->newQuery();

        // Filtros
        if (!empty($validated['code'])) {
            // Si el código es exacto: where; si quieres búsqueda parcial, usa like
            $query->where('code', $validated['code']);
            // Para búsqueda parcial:
            // $query->where('code', 'like', '%' . $validated['code'] . '%');
        }

        if (!empty($validated['query_type'])) {
            $query->where('query_type', $validated['query_type']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        // Orden
        $query->orderBy($sortBy, $sortDir);

        // Paginación
        $paginator = $query->paginate($perPage)->appends($request->query());

        // Uso de Resource para transformar los tickets
        return response()->json([
            'data' => TicketResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'sort_by'      => $sortBy,
                'sort_dir'     => $sortDir,
            ],
        ]);
    }
}
