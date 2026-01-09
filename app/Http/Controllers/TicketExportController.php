<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Http\Requests\TicketExportRequest;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketExportController extends Controller
{
    public function __invoke(TicketExportRequest $request): StreamedResponse | JsonResponse
    {
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $fileName = (!$start && !$end) ? "tickets.xlsx" : "tickets_{$start}_a_{$end}.xlsx";

        $export = new TicketsExport($start, $end);
        if ($export->query()->count() === 0) {
            return response()->json(['message' => 'No hay tickets para exportar'], 404);
        }

        $export = Excel::download($export, $fileName);

        $file = $export->getFile();

        $filename = "tickets_{$start}_a_{$end}.xlsx";

        return response()->streamDownload(
            fn() => fopen($file->getPathname(), 'r+'),
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            ]
        );
    }
}
