<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    public const SOURCE_HLS = 'hls';

    public const SOURCE_YOUTUBE = 'youtube';

    public const SOURCE_TYPES = [
        self::SOURCE_HLS,
        self::SOURCE_YOUTUBE,
    ];

    protected $fillable = [
        'lesson_id',
        'tenant_id',
        'title',
        'source_type',
        'youtube_video_id',
        'youtube_url',
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

    public function isHls(): bool
    {
        return ($this->source_type ?? self::SOURCE_HLS) === self::SOURCE_HLS;
    }

    public function isYoutube(): bool
    {
        return $this->source_type === self::SOURCE_YOUTUBE;
    }

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
