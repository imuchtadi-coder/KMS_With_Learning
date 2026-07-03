<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use Auditable;
    protected $fillable = [
        'user_id',
        'course_id',
        'gradebook_item_id',
        'score',
        'percentage',
        'letter_grade',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function gradebookItem(): BelongsTo
    {
        return $this->belongsTo(GradebookItem::class);
    }
}
