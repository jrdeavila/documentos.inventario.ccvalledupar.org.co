<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use App\TicketQueryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTicketRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::check();
    }
    public function rules(): array
    {
        return [
            'code' => 'nullable|integer',
            'query_type' => 'nullable|in:' . implode(
                ',',
                array_map(
                    fn($t) => $t->value,
                    TicketQueryType::cases()
                )
            ),
            'volume' => 'nullable|integer|min:1',
            'row' => 'nullable|string|size:1|regex:/^[A-N]$/i',
            'locker' => 'nullable|integer|min:1|max:50',
        ];
    }

    public function updateTicket(Ticket $ticket): Ticket
    {
        $ticket->code = $this->code ?? $ticket->code;
        $ticket->query_type = $this->query_type ?? $ticket->query_type;
        $ticket->volume = $this->volume ?? $ticket->volume;
        $ticket->row = $this->row ?? $ticket->row;
        $ticket->locker = $this->locker ?? $ticket->locker;
        $ticket->save();
        return $ticket;
    }
}
