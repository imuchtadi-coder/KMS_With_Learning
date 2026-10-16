<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionReply extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'discussion_id',
        'content',
        'created_by',
        'is_solution',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_solution' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
