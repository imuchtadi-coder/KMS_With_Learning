<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isTenantUser = $user && !$user->hasRole('superadmin') && $user->tenant_id;
        $cookieName = $isTenantUser
            ? 'kitsuluh_language_tenant_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $user->tenant_id)
            : 'kitsuluh_language';

        $locale = $request->cookie($cookieName, 'id');
        app()->setLocale(in_array($locale, ['id', 'en'], true) ? $locale : 'id');

        return $next($request);
    }
}
