<?php

namespace App\Traits;

trait HasTenantStorage
{
    public static function tenantPath(string $subpath, ?string $tenantId = null): string
    {
        $tenantId ??= auth()->user()?->tenant_id;

        return 'tenants/' . $tenantId . '/' . ltrim($subpath, '/');
    }
}
