<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Le inyectamos el header al request ANTES de que siga su camino
        $request->headers->set('Accept', 'application/json');

        // $next($request) le dice a Laravel: "listo, que siga al próximo middleware o al controlador"
        return $next($request);
    }
}
