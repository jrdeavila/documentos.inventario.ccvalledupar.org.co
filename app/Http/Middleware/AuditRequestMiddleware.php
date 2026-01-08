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
            if ($response instanceof \Illuminate\Http\JsonResponse) {

                Audit::log([
                    'action'  => 'request',
                    'status'  => 'success',
                    'message' => $response->status()
                ]);
            } else {
                Audit::log([
                    'action'  => 'request',
                    'status'  => 'success',
                    'message' => '200'
                ]);
            }


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
