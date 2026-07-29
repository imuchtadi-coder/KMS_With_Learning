<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\HandleCors;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->web(prepend: [
            HandleInertiaRequests::class,
        ]);

        $middleware->api(prepend: [
            HandleCors::class,
            SecurityHeaders::class,
        ]);

        // Register role middleware
        $middleware->alias([
            'role' => CheckRole::class,
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
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $request->wantsJson() && ! $request->expectsJson()) {
                $status = $e->getStatusCode();

                return Inertia::render('Errors/Error', ['status' => $status])->toResponse($request);
            }
        });

        // Render JSON responses for API errors
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->wantsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'Server Error';

                if (! config('app.debug')) {
                    $message = match ($status) {
                        401 => 'Unauthenticated.',
                        403 => 'Forbidden.',
                        404 => 'Resource not found.',
                        409 => 'Request conflict.',
                        422 => 'The submitted data is invalid.',
                        429 => 'Too many requests.',
                        default => $status >= 500 ? 'Server Error' : 'Request failed.',
                    };
                }

                return response()->json([
                    'message' => $message,
                    'status' => $status,
                ], $status);
            }
        });
    })->create();
