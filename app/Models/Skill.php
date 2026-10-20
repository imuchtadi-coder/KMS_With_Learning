<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Skill extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'category',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($skill) {
            if (empty($skill->slug)) {
                $baseSlug = Str::slug($skill->name);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('tenant_id', $skill->tenant_id)->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $skill->slug = $slug;
            }
        });
    }

    public function userSkills(): HasMany
    {
        return $this->hasMany(UserSkill::class);
    }

    public function experts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills')
            ->withPivot(['proficiency_level', 'endorsements_count', 'is_verified', 'verified_at'])
            ->withTimestamps();
    }

    public function scopeForTenant($query, ?string $tenantId = null)
    {
        $tenantId ??= auth()->user()?->tenant_id;
        return $query->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        });
    }
}
