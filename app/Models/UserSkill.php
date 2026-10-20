<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSkill extends Model
{
    protected $fillable = [
        'user_id',
        'skill_id',
        'proficiency_level',
        'endorsements_count',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'endorsements_count' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function endorsements(): HasMany
    {
        return $this->hasMany(SkillEndorsement::class);
    }
}
