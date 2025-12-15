<?php

namespace App\Http\Middleware;

use Closure;
use Laravel\Passport\Client;

class ValidateOAuthClient
{
    public function handle($request, Closure $next)
    {
        // Aplica sólo al endpoint de emisión de token de Passport
        if (! $request->is('oauth/token')) {
            return $next($request);
        }

        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        if (! $clientId || ! $clientSecret) {
            return response()->json([
                'message' => 'El origen no está permitido: faltan credenciales del cliente (client_id/client_secret).',
            ], 403);
        }

        $client = Client::query()
            ->where('id', $clientId)
            ->where('secret', $clientSecret)
            ->where('revoked', false)
            ->first();

        if (! $client) {
            return response()->json([
                'message' => 'El origen no está permitido: credenciales del cliente inválidas.',
            ], 403);
        }

        return $next($request);
    }
}
