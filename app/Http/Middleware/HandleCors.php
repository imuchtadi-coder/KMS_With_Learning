<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\HandleCors as Middleware;

class HandleCors extends Middleware
{
    /**
     * The URIs that should be excluded from CORS handling.
     */
    protected array $except = [
        //
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, $next)
    {
        $configuredOrigins = config('cors.allowed_origins', []);
        $allowedOrigins = is_array($configuredOrigins)
            ? $configuredOrigins
            : array_map('trim', explode(',', $configuredOrigins));
        $origin = $request->headers->get('Origin');

        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin ?? '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Max-Age', '86400')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request);
    }
}
