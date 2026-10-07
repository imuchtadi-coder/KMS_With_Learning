<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomainOrAuthenticatedTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenancy()->initialized) {
            return $next($request);
        }

        $centralDomains = config('tenancy.central_domains', []);

        if (! in_array($request->getHost(), $centralDomains, true)) {
            return app(InitializeTenancyByDomain::class)->handle($request, $next);
        }

        $tenant = $request->user()?->tenant;

        abort_unless($tenant, 404);

        tenancy()->initialize($tenant);

        try {
            return $next($request);
        } finally {
            tenancy()->end();
        }
    }
}
