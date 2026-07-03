<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use Auditable;
    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'description',
        'passing_grade',
        'time_limit',
        'max_attempts',
        'shuffle_questions',
        'show_result',
        'published',
        'is_exam',
        'start_date',
        'end_date',
        'grace_period',
        'randomize_options',
        'prevent_copy_paste',
        'require_webcam',
        'created_by',
    ];

    protected $casts = [
        'passing_grade' => 'integer',
        'time_limit' => 'integer',
        'max_attempts' => 'integer',
        'shuffle_questions' => 'boolean',
        'show_result' => 'boolean',
        'published' => 'boolean',
        'is_exam' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'grace_period' => 'integer',
        'randomize_options' => 'boolean',
        'prevent_copy_paste' => 'boolean',
        'require_webcam' => 'boolean',
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

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_questions')
            ->withPivot('order', 'points')
            ->orderBy('quiz_questions.order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function gradebookItem()
    {
        return $this->morphOne(GradebookItem::class, 'item');
    }
}
