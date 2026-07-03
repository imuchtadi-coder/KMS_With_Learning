<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DiscussionReply;

class DiscussionReplyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['instructor', 'student', 'tenant_admin']);
    }

    public function view(User $user, DiscussionReply $reply): bool
    {
        return $user->tenant_id === $reply->discussion->course->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['instructor', 'student']);
    }

    public function update(User $user, DiscussionReply $reply): bool
    {
        return $user->id === $reply->created_by;
    }

    public function delete(User $user, DiscussionReply $reply): bool
    {
        return $user->id === $reply->created_by || $user->hasRole('tenant_admin');
    }
}
