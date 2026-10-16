<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'course_id',
        'lesson_id',
        'category_id',
        'title',
        'content',
        'created_by',
        'is_pinned',
        'is_locked',
        'is_resolved',
        'accepted_reply_id',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'is_resolved' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function latestReply()
    {
        return $this->hasOne(DiscussionReply::class)->latestOfMany();
    }

    public function acceptedReply(): BelongsTo
    {
        return $this->belongsTo(DiscussionReply::class, 'accepted_reply_id');
    }

    public function scopeGeneral($query)
    {
        return $query->whereNull('course_id');
    }

    public function scopeCourseSpecific($query)
    {
        return $query->whereNotNull('course_id');
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
}
