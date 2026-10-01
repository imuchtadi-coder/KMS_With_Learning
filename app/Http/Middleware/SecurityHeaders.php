<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent clickjacking
        // $response->headers->set(
        //     'X-Frame-Options',
        //     'DENY'
        // );
        $isIframePreview =
            $request->routeIs('student.materials.preview') ||
            $request->routeIs('instructor.assignments.submissions.preview');

        if ($isIframePreview) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        // Prevent MIME type sniffing
        $response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

        // Enable XSS protection
        $response->headers->set(
            'X-XSS-Protection',
            '1; mode=block'
        );

        // Strict Transport Security (HSTS)
        // Only enabled in production.
        if (config('app.env') === 'production') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        /*
         * Content Security Policy
         *
         * Vite development server is allowed only outside production.
         *
         * IMPORTANT:
         * Do not use [::1]:5173 here because Chromium reports it
         * as an invalid CSP source.
         *
         * Vite is configured to use:
         * http://127.0.0.1:5173
         */

        $scriptSrc = [
            "'self'",
            "'unsafe-inline'",
            'https://cdn.jsdelivr.net',
            'https://www.youtube.com',
            'https://s.ytimg.com',
        ];

        $connectSrc = [
            "'self'",
        ];

        /*
         * Development-only Vite configuration
         */
        if (config('app.env') !== 'production') {

            // Vite development server
            $scriptSrc[] = 'http://localhost:5173';
            $scriptSrc[] = 'http://127.0.0.1:5173';

            // Vite HMR / WebSocket
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'http://127.0.0.1:5173';
            $connectSrc[] = 'ws://localhost:5173';
            $connectSrc[] = 'ws://127.0.0.1:5173';
        }

        $appUrl = config('app.url');

        $appOrigin = null;

        if ($appUrl) {
            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            $host = parse_url($appUrl, PHP_URL_HOST);
            $port = parse_url($appUrl, PHP_URL_PORT);

            if ($scheme && $host) {
                $appOrigin = "{$scheme}://{$host}";

                if ($port) {
                    $appOrigin .= ":{$port}";
                }
            }
        }

        /*
         * Build Content Security Policy
         */
        $contentSecurityPolicy = [
            "default-src 'self'",

            "base-uri 'self'",

            "object-src 'none'",

            "frame-ancestors " . ($isIframePreview ? "'self'" : "'none'"),

            "form-action 'self'",

            'script-src ' . implode(' ', $scriptSrc),

            "style-src 'self' 'unsafe-inline'",

            "img-src 'self' data: blob:"
            . ($appOrigin ? " {$appOrigin}" : '')
            . " https://ui-avatars.com",

            // "img-src 'self' data: blob: https://ui-avatars.com",

            "font-src 'self' data: http://localhost:5173 http://127.0.0.1:5173",

            'connect-src ' . implode(' ', $connectSrc),

            "media-src 'self' blob:",

            "worker-src 'self' blob:",

            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com",
        ];

        /*
         * Set Content Security Policy header
         */
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', $contentSecurityPolicy)
        );

        // Referrer Policy
        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );

        // Permissions Policy
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}