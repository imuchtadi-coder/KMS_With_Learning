<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->tenant_id !== $article->tenant_id) {
            return false;
        }

        if ($article->status === 'published') {
            return true;
        }

        return $user->id === $article->author_id || $user->hasRole(['tenant_admin', 'superadmin', 'instructor']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'tenant_admin', 'instructor', 'student']);
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $article->tenant_id
            && ($user->id === $article->author_id || $user->hasRole('tenant_admin'));
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $article->tenant_id
            && ($user->id === $article->author_id || $user->hasRole('tenant_admin'));
    }

    public function review(User $user, Article $article): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Must be in same tenant and have SME/reviewer role (tenant_admin or instructor)
        return $user->tenant_id === $article->tenant_id
            && $user->hasRole(['tenant_admin', 'instructor']);
    }

    public function submitReview(User $user, Article $article): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $article->tenant_id
            && ($user->id === $article->author_id || $user->hasRole('tenant_admin'));
    }
}
