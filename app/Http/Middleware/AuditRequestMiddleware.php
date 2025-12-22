<?php

namespace App\Http\Middleware;

use App\Support\Audit;
use Closure;
use Throwable;

class AuditRequestMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $response = $next($request);

            Audit::log([
                'action'  => 'request',
                'status'  => 'success',
                'message' => $response->status(),
            ]);

            return $response;
        } catch (Throwable $e) {
            Audit::log([
                'action'  => 'request',
                'status'  => 'fail',
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
