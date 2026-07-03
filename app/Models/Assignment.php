<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use Auditable;
    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'description',
        'instructions',
        'file_types',
        'max_file_size',
        'deadline',
        'passing_grade',
        'max_score',
        'created_by',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'max_file_size' => 'integer',
        'passing_grade' => 'integer',
        'max_score' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function gradebookItem()
    {
        return $this->morphOne(GradebookItem::class, 'item');
    }
}
