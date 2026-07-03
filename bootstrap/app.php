<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Register role middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unknown tenant domains
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (str_contains($e->getMessage(), 'tenant')) {
                return Inertia::render('Errors/Error', ['status' => 404])->toResponse($request);
            }
        });

        $exceptions->report(function (Throwable $e) {
            // Send to Sentry in production
            if (app()->bound('sentry') && app()->environment('production')) {
                app('sentry')->captureException($e);
            }
        });

        // Render Inertia error pages for HTTP exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if (!$request->wantsJson() && !$request->expectsJson()) {
                $status = $e->getStatusCode();
                return Inertia::render('Errors/Error', ['status' => $status])->toResponse($request);
            }
        });

        // Render JSON responses for API errors
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Server Error',
                    'status' => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500,
                ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
            }
        });
    })->create();
