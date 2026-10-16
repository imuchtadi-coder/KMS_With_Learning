<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDemoMutation
{
    /**
     * Handle an incoming request.
     * Prevents users in demo sandbox session from altering sensitive data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('is_demo_session')) {
            // Block sensitive destructive operations in demo mode
            if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
                if ($request->is('*password*', '*admin/tenants*', '*users*') && $request->isMethod('delete')) {
                    return back()->with('error', 'Aksi modifikasi dan penghapusan data penting dibatasi pada mode Demo Sandbox.');
                }
            }
        }

        return $next($request);
    }
}
