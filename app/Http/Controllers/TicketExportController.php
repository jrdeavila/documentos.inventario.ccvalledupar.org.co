<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Http\Requests\TicketExportRequest;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketExportController extends Controller
{
    public function __invoke(TicketExportRequest $request): JsonResponse | BinaryFileResponse
    {
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $export = new TicketsExport($start, $end);

        if ($export->query()->count() === 0) {
            return response()->json(['message' => 'No hay tickets para exportar'], 404);
        }

        $fileName = (!$start && !$end) ? "tickets.xlsx" : "tickets_{$start}_a_{$end}.xlsx";

        // Excel::download ya devuelve la respuesta correcta con los headers necesarios
        return Excel::download($export, $fileName);
    }
}
