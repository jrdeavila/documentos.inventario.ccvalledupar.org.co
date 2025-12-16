<?php

namespace App\Http\Controllers;

use App\Exports\TicketsExport;
use App\Http\Requests\TicketExportRequest;
use Maatwebsite\Excel\Facades\Excel;

class TicketExportController extends Controller
{
    public function __invoke(TicketExportRequest $request)
    {
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        $fileName = "tickets_{$start}_a_{$end}.xlsx";

        return Excel::download(new TicketsExport($start, $end), $fileName);
    }
}
