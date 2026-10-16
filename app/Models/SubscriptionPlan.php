<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'slug',
        'name',
        'price',
        'billing_period',
        'description',
        'badge',
        'features',
        'max_users',
        'max_courses',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'features' => 'array',
        'is_active' => 'boolean',
        'max_users' => 'integer',
        'max_courses' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get all tenants subscribed to this plan
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id', 'id');
    }
}
