<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['instructor', 'tenant_admin', 'superadmin', 'student']);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('superadmin')) return true;
        return $user->tenant_id === $assignment->course->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['instructor', 'tenant_admin']);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('superadmin')) return true;
        return ($user->id === $assignment->created_by && $user->tenant_id === $assignment->course->tenant_id)
            || ($user->hasRole('tenant_admin') && $user->tenant_id === $assignment->course->tenant_id);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        return $user->hasRole('student') && $user->tenant_id === $assignment->course->tenant_id;
    }

    public function grade(User $user, Assignment $assignment): bool
    {
        return ($user->id === $assignment->created_by && $user->tenant_id === $assignment->course->tenant_id)
            || ($user->hasRole('tenant_admin') && $user->tenant_id === $assignment->course->tenant_id);
    }
}
