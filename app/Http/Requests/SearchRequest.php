<?php

namespace App\Http\Requests;

use App\Models\MregEstInscrito;
use App\Models\MregEstProponente;
use App\TicketQueryType;
use Illuminate\Foundation\Http\FormRequest;

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
        if (in_array($this->query_type, [TicketQueryType::NONPROFIT->value, TicketQueryType::COMMERCIAL->value])) {
            $query = MregEstInscrito::query()->where("id", $this->code)->get()->withEstablishments();
        } else {
            $query = MregEstProponente::query()->where("id", $this->code)->get();
        }
        return $query;
    }
}
