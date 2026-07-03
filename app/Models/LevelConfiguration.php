<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelConfiguration extends Model
{
    protected $fillable = [
        'tenant_id',
        'level',
        'xp_required',
        'title',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp_required' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
