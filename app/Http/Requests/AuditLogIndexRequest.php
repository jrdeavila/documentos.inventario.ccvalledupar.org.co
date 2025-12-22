<?php

namespace App\Http\Requests;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && User::find(Auth::id())->can('viewAny', Audit::class);
    }

    public function rules(): array
    {
        return [
            'user_id'         => ['nullable', 'integer'],
            'action'          => ['nullable', 'string', 'max:50'],
            'status'          => ['nullable', 'in:success,fail'],
            'auditable_type'  => ['nullable', 'string', 'max:255'],
            'auditable_id'    => ['nullable', 'integer'],
            'ip'              => ['nullable', 'ip'],
            'method'          => ['nullable', 'in:GET,POST,PUT,PATCH,DELETE,OPTIONS'],
            'username'        => ['nullable', 'string', 'max:255'], // si lo guardas en new_values
            'date_from'       => ['nullable', 'date'],
            'date_to'         => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort_by'         => ['nullable', 'in:id,created_at,action,status,user_id'],
            'sort_dir'        => ['nullable', 'in:asc,desc'],
            'per_page'        => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'            => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'per_page' => $this->per_page ?? 20,
            'sort_by'  => $this->sort_by ?? 'id',
            'sort_dir' => $this->sort_dir ?? 'desc',
        ]);
    }
}
