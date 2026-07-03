<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuizQuestion extends Pivot
{
    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question_id',
        'order',
        'points',
    ];

    protected $casts = [
        'order' => 'integer',
        'points' => 'integer',
    ];
}
