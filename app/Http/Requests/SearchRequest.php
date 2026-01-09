<?php

namespace App\Http\Requests;

use App\Models\MregEstInscrito;
use App\Models\MregEstProponente;
use App\Support\Audit;
use App\TicketQueryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string',
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
        Audit::log([
            'action' => 'search',
            'user_id' => Auth::user()->id,
            'message' => $this->query_type,
            'status' => 'success',
        ]);

        if (in_array($this->query_type, [TicketQueryType::NONPROFIT->value, TicketQueryType::COMMERCIAL->value])) {
            $query = MregEstInscrito::query()->where("id", $this->code)->get()->withEstablishments();
        } else {
            $query = MregEstProponente::query()->where("id", $this->code)->get();
        }
        return $query;
    }
}
