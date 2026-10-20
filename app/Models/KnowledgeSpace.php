<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KnowledgeSpace extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'visibility',
        'created_by',
        'manager_id',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($space) {
            if (empty($space->slug)) {
                $baseSlug = Str::slug($space->name);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('tenant_id', $space->tenant_id)->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $space->slug = $slug;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'space_id')->orderBy('order');
    }

    public function rootArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'space_id')->whereNull('parent_id')->orderBy('order');
    }

    public function scopeForTenant($query, ?string $tenantId = null)
    {
        $tenantId ??= auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }
}
