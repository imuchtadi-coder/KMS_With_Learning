<?php

namespace App\Policies;

use App\Models\DiscussionReply;
use App\Models\User;

class DiscussionReplyPolicy
{
    private function replyTenantId(DiscussionReply $reply): ?string
    {
        return $reply->discussion->tenant_id ?? $reply->discussion->course?->tenant_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['instructor', 'student', 'tenant_admin', 'superadmin']);
    }

    public function view(User $user, DiscussionReply $reply): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->replyTenantId($reply);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['instructor', 'student', 'tenant_admin', 'superadmin']);
    }

    public function update(User $user, DiscussionReply $reply): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->replyTenantId($reply)
            && $user->id === $reply->created_by;
    }

    public function delete(User $user, DiscussionReply $reply): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->tenant_id === $this->replyTenantId($reply)
            && ($user->id === $reply->created_by || $user->hasRole('tenant_admin'));
    }
}
