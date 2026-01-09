<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketsExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function query()
    {
        // Si las fechas son del mismo día, devuelve todos los tickets
        if (is_null($this->startDate) && is_null($this->endDate)) {
            return Ticket::query()->orderBy('created_at', 'asc');
        }
        // Filtra por created_at entre las fechas (incluyendo todo el día final)
        return Ticket::query()
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->orderBy('created_at', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Volumen',
            'Fila',
            'Locker',
            'Tipo de consulta',
            'Creado en',
            'Actualizado en',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->code,
            $ticket->volume,
            $ticket->row,
            $ticket->locker,
            $ticket->query_type,
            optional($ticket->created_at)->format('Y-m-d H:i:s'),
            optional($ticket->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
