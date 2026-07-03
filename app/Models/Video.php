<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $fillable = [
        'lesson_id',
        'tenant_id',
        'title',
        'original_path',
        'hls_path',
        'thumbnail',
        'duration_seconds',
        'status',
        'error_message',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];

    public function getHlsUrlAttribute(): ?string
    {
        if (!$this->hls_path) {
            return null;
        }

        $path = $this->hls_path;
        if (!str_ends_with($path, '.m3u8')) {
            $path .= '/master.m3u8';
        }

        return asset('storage/' . $path);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
