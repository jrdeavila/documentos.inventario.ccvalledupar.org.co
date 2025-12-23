<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use App\TicketQueryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateManyTicketRequest extends FormRequest
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
            ],
            'query_type' => ['required', 'in:' . implode(',', array_map(fn($t) => $t->value, TicketQueryType::cases()))],
            'status' => ['required', 'string'],
            'tickets' => ['required', 'array'],
            'tickets.*.volume' => [
                'required',
                'integer',
                'min:1',
                // Validar que code, query_type y volume sean únicos
                function ($attribute, $value, $fail) {
                    $code = $this->input('code');
                    $query_type = $this->input('query_type');
                    $ticket = Ticket::where('code', $code)->where('query_type', $query_type)->where('volume', $value)->first();
                    if ($ticket) {
                        $fail('El tomo en la posición :position ya está en uso.');
                    }
                }
            ],
            'tickets.*.row' => ['required', 'string', 'size:1', 'regex:/^[A-N]$/i'],
            'tickets.*.locker' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código es requerido.',
            'code.integer' => 'El código debe ser un número.',
            'query_type.required' => 'El tipo de consulta es requerido.',
            'query_type.in' => 'El tipo de consulta es inválido.',
            'status.required' => 'El estado es requerido.',
            'status.string' => 'El estado debe ser una cadena de texto.',
            'tickets.required' => 'Los tickets son requeridos.',
            'tickets.*.volume.required' => 'El tomo del registro en la posicion :position es requerido.',
            'tickets.*.volume.integer' => 'El tomo del registro en la posicion :position debe ser un número.',
            'tickets.*.volume.min' => 'El tomo del registro en la posicion :position debe ser mayor o igual a 1.',
            'tickets.*.row.required' => 'La fila del registro en la posicion :position es requerido.',
            'tickets.*.row.string' => 'La fila del registro en la posicion :position debe ser una letra.',
            'tickets.*.row.size' => 'La fila del registro en la posicion :position debe ser una letra.',
            'tickets.*.row.regex' => 'La fila del registro en la posicion :position debe ser una letra.',
            'tickets.*.locker.required' => 'El locker del registro en la posicion :position es requerido.',
            'tickets.*.locker.integer' => 'El locker del registro en la posicion :position debe ser un número.',
            'tickets.*.locker.min' => 'El locker del registro en la posicion :position debe ser mayor o igual a 1.',
            'tickets.*.locker.max' => 'El locker del registro en la posicion :position debe ser menor o igual a 50.',
        ];
    }

    public function createManyTickets()
    {
        $tickets = [];

        try {

            DB::beginTransaction();

            foreach ($this->input('tickets') as $key => $ticket) {
                $tickets[$key] = $ticket;
                $tickets[$key]['code'] = $this->input('code');
                $tickets[$key]['query_type'] = $this->input('query_type');
                $tickets[$key]['user_id'] = Auth::id();
                $tickets[$key]['status'] = $this->input('status');
                $tickets[$key]['created_at'] = now();
                $tickets[$key]['updated_at'] = now();
            }
            DB::table('tickets')->insert($tickets);
            DB::commit();
            $tickets = Ticket::where('code', $this->input('code'))->where('query_type', $this->input('query_type'))->get();
            return $tickets;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
