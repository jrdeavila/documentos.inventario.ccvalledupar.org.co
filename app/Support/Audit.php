<?php

namespace App\Support;

use App\Models\Audit as ModelsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Audit
{
  public static function log(array $data, ?Request $request = null): ModelsAudit
  {
    $request = $request ?: request();

    return ModelsAudit::create([
      'user_id'        => $data['user_id'] ?? optional(Auth::user())->id,
      'auditable_type' => $data['auditable_type'] ?? null,
      'auditable_id'   => $data['auditable_id'] ?? null,
      'action'         => $data['action'],
      'old_values'     => $data['old_values'] ?? null,
      'new_values'     => $data['new_values'] ?? null,
      'ip_address'     => $data['ip_address'] ?? ($request?->ip()),
      'user_agent'     => $data['user_agent'] ?? ($request?->userAgent()),
      'url'            => $data['url'] ?? ($request?->fullUrl()),
      'method'         => $data['method'] ?? ($request?->method()),
      'status'         => $data['status'] ?? 'success',
      'message'        => $data['message'] ?? null,
    ]);
  }
}
