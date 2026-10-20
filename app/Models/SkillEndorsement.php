<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillEndorsement extends Model
{
    protected $fillable = [
        'user_skill_id',
        'endorsed_by',
        'notes',
    ];

    public function userSkill(): BelongsTo
    {
        return $this->belongsTo(UserSkill::class);
    }

    public function endorser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }
}
