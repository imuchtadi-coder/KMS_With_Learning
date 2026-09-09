<?php

namespace App\Policies;

use App\Models\Discussion;
use App\Models\User;

class DiscussionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['instructor', 'student', 'tenant_admin']);
    }

    public function view(User $user, Discussion $discussion): bool
    {
        return $user->tenant_id === $discussion->course->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->hasRole(['instructor', 'student']);
    }

    public function update(User $user, Discussion $discussion): bool
    {
        return $user->tenant_id === $discussion->course->tenant_id
            && ($user->id === $discussion->created_by || $user->hasRole('tenant_admin'));
    }

    public function delete(User $user, Discussion $discussion): bool
    {
        return $user->tenant_id === $discussion->course->tenant_id
            && ($user->id === $discussion->created_by || $user->hasRole('tenant_admin'));
    }

    public function reply(User $user, Discussion $discussion): bool
    {
        return $user->tenant_id === $discussion->course->tenant_id
            && ! $discussion->is_locked
            && $user->hasRole(['instructor', 'student']);
    }

    public function pin(User $user, Discussion $discussion): bool
    {
        return $user->tenant_id === $discussion->course->tenant_id
            && $user->hasRole(['instructor', 'tenant_admin']);
    }
}
