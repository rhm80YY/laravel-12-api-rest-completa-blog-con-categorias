<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit; // <--- IMPORTACIÓN CORRECTA
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       RateLimiter::for('api', function (Request $request) {
        // Si el usuario es admin, le damos 200 peticiones por minuto
        if ($request->user()?->hasRole('admin')) {
            return Limit::perMinute(200)->by($request->user()->id);
        }

        // Para usuarios autenticados normales, 60 peticiones por minuto
        // Para invitados, 10 peticiones por minuto (protección extra)
        return $request->user()
            ? Limit::perMinute(60)->by($request->user()->id)
            : Limit::perMinute(10)->by($request->ip());
    });
    }
}
