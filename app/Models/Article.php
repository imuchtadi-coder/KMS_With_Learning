<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'version',
        'excerpt',
        'content',
        'category_id',
        'author_id',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'views_count',
        'published_at',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $baseSlug = Str::slug($article->title);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('tenant_id', $article->tenant_id)->where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $article->slug = $slug;
            }

            if ($article->status === 'published' && empty($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ArticleAttachment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag')->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArticleVersion::class)->orderBy('id', 'desc');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeForTenant($query, ?string $tenantId = null)
    {
        $tenantId ??= auth()->user()?->tenant_id;
        return $query->where('tenant_id', $tenantId);
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }
}
