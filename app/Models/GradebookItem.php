<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradebookItem extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'type',
        'item_id',
        'weight',
        'max_score',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
