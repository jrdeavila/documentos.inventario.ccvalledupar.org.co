<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateToken
{
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Necesitas estar autenticado para acceder a este recurso.',
            ], 401);
        }
        return $next($request);
    }
}
