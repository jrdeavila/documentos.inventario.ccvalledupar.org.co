<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogIndexRequest;
use App\Models\Audit;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Audit::class);

        $q = Audit::query();

        $filters = $request->validated();

        // Filtros directos
        if (!empty($filters['user_id']))        $q->where('user_id', $filters['user_id']);
        if (!empty($filters['action']))         $q->where('action', $filters['action']);
        if (!empty($filters['status']))         $q->where('status', $filters['status']);
        if (!empty($filters['auditable_type'])) $q->where('auditable_type', $filters['auditable_type']);
        if (!empty($filters['auditable_id']))   $q->where('auditable_id', $filters['auditable_id']);
        if (!empty($filters['method']))         $q->where('method', $filters['method']);
        if (!empty($filters['ip']))             $q->where('ip_address', $filters['ip']);

        // Rango de fechas
        if (!empty($filters['date_from'])) $q->where('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->where('created_at', '<=', $filters['date_to']);

        // Búsqueda por username almacenado en new_values JSON
        if (!empty($filters['username'])) {
            // MySQL/MariaDB: JSON_EXTRACT
            $q->where(function ($qq) use ($filters) {
                $qq->where('new_values->username', $filters['username'])
                    ->orWhere('old_values->username', $filters['username']);
            });
        }

        // Orden
        $sortBy  = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $q->orderBy($sortBy, $sortDir);

        $perPage = $filters['per_page'] ?? 20;

        $paginator = $q->paginate($perPage)->appends($request->query());

        return response()->json([
            'data'  => $paginator->items(),
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $log = Audit::findOrFail($id);
        $this->authorize('view', $log);

        return response()->json([
            'data' => $log,
        ]);
    }
}
