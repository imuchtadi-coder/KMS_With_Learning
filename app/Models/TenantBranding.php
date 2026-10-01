<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBranding extends Model
{
    protected $fillable = [
        'tenant_id',
        'agency_name',
        'tagline',
        'logo_path',
        'certificate_logo_path',
    ];

    /**
     * Get the tenant that owns this branding.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}