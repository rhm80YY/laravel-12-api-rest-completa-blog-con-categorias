<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Nuestro middleware del Día 5
        $middleware->api(append: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
   
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 1. LA MAGIA: Forzar JSON en toda la API. 
        // Esto evita que Laravel intente redirigir a rutas web como "login"
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*');
        });

        // 2. Ahora sí, personalizamos el 401
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'No tenés un token válido para acceder a este recurso.',
                'code' => 401
            ], 401);
        });

        // 3. Manejo de Errores de Validación (Error 422)
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->errors(),
                'code' => 422
            ], 422);
        });

        // 4. Manejo de Modelo No Encontrado (Error 404)
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'The requested resource could not be found.',
                'code' => 404
            ], 404);
        });

    })->create();