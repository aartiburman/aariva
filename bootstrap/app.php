<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'admin', // Exclude NCM webhook callback
        ]);

        // ✅ Register route middleware aliases here
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'auth' => \App\Http\Middleware\Auth::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            \App\Http\Middleware\UserActivityMiddleware::class,
            \App\Http\Middleware\GeoLocationMiddleware::class,
            \App\Http\Middleware\LanguageMiddleware::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\SingleSessionMiddleware::class,
            \App\Http\Middleware\RedirectMiddleware::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'session expire login again',
                ], 501);
            }
        });

         $exceptions->render(function (TokenMismatchException $e, $request) {

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Session expired. Please login again.'
            ], 419);
        }

        return redirect()
            ->route('login')
            ->with('error', 'Your session has expired. Please login again.');
    });

        
    })
    
    ->create();
