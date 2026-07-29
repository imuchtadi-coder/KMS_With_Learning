<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct() {}

    /**
     * Only instructor can create course
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null && $user->hasRole('instructor');
    }

    /**
     * Only course creator can edit (must be same tenant)
     */
    public function update(User $user, Course $course): bool
    {
        return ($user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id) ||
               ($user->hasRole('tenant_admin') &&
                $user->tenant_id === $course->tenant_id);
    }

    /**
     * Only course creator or tenant admin can delete (must be same tenant)
     */
    public function delete(User $user, Course $course): bool
    {
        return ($user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id) ||
               ($user->hasRole('tenant_admin') &&
                $user->tenant_id === $course->tenant_id);
    }

    /**
     * Publish course (only tenant admin)
     */
    public function publish(User $user, Course $course): bool
    {
        return $user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id;
    }

    /**
     * View course
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->tenant_id === null || $user->tenant_id !== $course->tenant_id) {
            return false;
        }

        // Published courses - anyone in same tenant
        if ($course->published) {
            return true;
        }

        // Draft courses - only creator or tenant admin
        return $user->id === $course->created_by || $user->hasRole('tenant_admin');
    }
}
