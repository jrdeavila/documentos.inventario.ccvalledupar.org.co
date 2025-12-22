<?php

use App\Http\Middleware\AuditRequestMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Middleware\ValidateOAuthClient;
use App\Http\Middleware\ValidateToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS Middleware
        $middleware->prepend(HandleCors::class);
        // Añade validación de origen (client_id/client_secret) al grupo API
        $middleware->appendToGroup('api', [
            ValidateOAuthClient::class,
            ValidateToken::class
        ]);
        //  Alias
        $middleware->alias([
            'audit' => AuditRequestMiddleware::class
        ]);
        // Puedes agregar rate limiting, etc., aquí
        // $middleware->appendToGroup('api', [\Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Detecta API por prefijo /api o Accept JSON
        $isApi = fn() => request()->expectsJson() || request()->is('api/*') || request()->is('oauth/*');

        // Bad Request
        $exceptions->render(function (BadRequestHttpException $e) use ($isApi) {
            Log::error($e);
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'Solicitud inválida.',
            ], 400);
        });


        // Validación (422)
        $exceptions->render(function (ValidationException $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors'  => $e->errors(),
            ], 422);
        });

        // No autenticado (401): evita redirecciones y devuelve JSON
        $exceptions->render(function (AuthenticationException $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'Necesitas estar autenticado para acceder a este recurso.',
            ], 401);
        });

        // Prohibido (403)
        $exceptions->render(function (AuthorizationException $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'No autorizado.',
            ], 403);
        });

        // Model not found -> 404
        $exceptions->render(function (ModelNotFoundException $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'Recurso no encontrado.',
            ], 404);
        });

        // Ruta no encontrada -> 404
        $exceptions->render(function (NotFoundHttpException $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'Ruta no encontrada.',
            ], 404);
        });

        // Token CSRF -> 419
        $exceptions->render(function (TokenMismatchException $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => 'La sesión ha expirado o el token no es válido.',
            ], 419);
        });



        // HttpException genérica (400, 405, 409, 429, 502, 503, etc.)
        $exceptions->render(function (HttpExceptionInterface $e) use ($isApi) {
            if (! $isApi()) return null;
            $status = $e->getStatusCode();
            return response()->json([
                'message' => defaultMessageForStatus($status),
            ], $status);
        });

        // Cualquier otra excepción -> 500
        $exceptions->render(function (Throwable $e) use ($isApi) {
            if (! $isApi()) return null;
            return response()->json([
                'message' => app()->hasDebugModeEnabled()
                    ? ($e->getMessage() ?: 'Error interno del servidor.')
                    : 'Error interno del servidor.',
            ], 500);
        });
    })
    ->create();

/**
 * Mensajes por estado HTTP
 */
function defaultMessageForStatus(int $status): string
{
    return match ($status) {
        400 => 'Solicitud inválida.',
        401 => 'No autenticado.',
        403 => 'No autorizado.',
        404 => 'Recurso no encontrado.',
        405 => 'Método no permitido.',
        408 => 'Tiempo de espera agotado.',
        409 => 'Conflicto.',
        419 => 'La sesión ha expirado.',
        422 => 'Datos inválidos.',
        429 => 'Demasiadas solicitudes.',
        500 => 'Error interno del servidor.',
        502 => 'Bad Gateway.',
        503 => 'Servicio no disponible.',
        504 => 'Gateway Timeout.',
        default => 'Ocurrió un error.',
    };
}
