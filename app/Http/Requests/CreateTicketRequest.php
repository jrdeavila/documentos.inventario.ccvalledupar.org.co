<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use App\TicketQueryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateTicketRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'integer',
                'unique:' . Ticket::class . ',code,volume,query_type,' . $this->code . ',' . $this->volume . ',' . $this->query_type
            ],
            'query_type' => 'required|in:' . implode(
                ',',
                array_map(
                    fn($t) => $t->value,
                    TicketQueryType::cases()
                )
            ),
            'volume' => 'required|integer|min:1',
            'row' => 'required|string|size:1|regex:/^[A-N]$/i',
            'locker' => 'required|integer|min:1|max:50',
            // Validar que code y query_type sean únicos
            'code' => 'unique:' . Ticket::class . ',code,' . $this->code . ',id,query_type,' . $this->query_type,
            'status' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es requerido.',
            'code.integer' => 'El código debe ser un número.',
            'code.unique' => 'El código ya está en uso.',
            'volume.required' => 'El tomo es requerido.',
            'volume.integer' => 'El tomo debe ser un número.',
            'volume.min' => 'El tomo debe ser mayor o igual a 1.',
            'row.required' => 'La fila es requerida.',
            'row.string' => 'La fila debe ser una letra.',
            'row.size' => 'La fila debe ser una letra.',
            'row.regex' => 'La fila debe ser una letra.',
            'locker.required' => 'El locker es requerido.',
            'locker.integer' => 'El locker debe ser un número.',
            'locker.min' => 'El estante debe ser mayor o igual a 1.',
            'locker.max' => 'El estante debe ser menor o igual a 50.',
            'status.required' => 'El estado es requerido.',
            'status.string' => 'El estado debe ser una cadena de texto.',
        ];
    }

    public function createTicket(): Ticket
    {
        return Ticket::create([
            'code' => $this->code,
            'volume' => $this->volume,
            'row' => $this->row,
            'locker' => $this->locker,
            'query_type' => $this->query_type,
            'user_id' => Auth::id(),
            'status' => $this->status
        ]);
    }
}
