<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpConfiguration extends Model
{
    protected $fillable = [
        'tenant_id',
        'action',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
