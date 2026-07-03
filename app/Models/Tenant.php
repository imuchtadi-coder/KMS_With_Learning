<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as StanclTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Domain;

class Tenant extends StanclTenant
{
    protected $fillable = ['id', 'name', 'data'];

    /**
     * Get all domains for this tenant
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Get all users assigned to this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }
}

