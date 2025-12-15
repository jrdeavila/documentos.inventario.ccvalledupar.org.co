<?php

namespace App\Http\Requests;

use App\Models\MregEstInscrito;
use App\TicketQueryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|integer',
            'query_type' => 'required|in:' . implode(
                ',',
                array_map(
                    fn($t) => $t->value,
                    TicketQueryType::cases()
                )
            ),
        ];
    }

    public function messages()
    {
        return [];
    }

    public function search()
    {
        $query = MregEstInscrito::query()->where("id", $this->code)->get()->withEstablishments();
        return $query;
    }
}
