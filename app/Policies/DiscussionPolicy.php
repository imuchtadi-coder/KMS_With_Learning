<?php

namespace App\Policies;

use App\Models\Discussion;
use App\Models\User;

class DiscussionPolicy
{
    private function discussionTenantId(Discussion $discussion): ?string
    {
        return $discussion->tenant_id ?? $discussion->course?->tenant_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['instructor', 'student', 'tenant_admin', 'superadmin']);
    }

    public function view(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->discussionTenantId($discussion);
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->hasRole(['instructor', 'student', 'tenant_admin', 'superadmin']);
    }

    public function update(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->discussionTenantId($discussion)
            && ($user->id === $discussion->created_by || $user->hasRole('tenant_admin'));
    }

    public function delete(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->discussionTenantId($discussion)
            && ($user->id === $discussion->created_by || $user->hasRole('tenant_admin'));
    }

    public function reply(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('superadmin')) {
            return ! $discussion->is_locked;
        }

        return $user->tenant_id === $this->discussionTenantId($discussion)
            && ! $discussion->is_locked
            && $user->hasRole(['instructor', 'student', 'tenant_admin']);
    }

    public function pin(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->discussionTenantId($discussion)
            && $user->hasRole(['instructor', 'tenant_admin']);
    }

    public function verifySolution(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->discussionTenantId($discussion)
            && ($user->id === $discussion->created_by || $user->hasRole(['instructor', 'tenant_admin']));
    }
}
