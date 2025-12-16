<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta según tu lógica de autenticación
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date'   => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'La fecha inicial es obligatoria.',
            'start_date.date' => 'La fecha inicial debe ser válida.',
            'start_date.date_format' => 'La fecha inicial debe tener formato Y-m-d.',
            'end_date.required' => 'La fecha final es obligatoria.',
            'end_date.date' => 'La fecha final debe ser válida.',
            'end_date.date_format' => 'La fecha final debe tener formato Y-m-d.',
            'end_date.after_or_equal' => 'La fecha final debe ser posterior o igual a la inicial.',
        ];
    }
}
