<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lesson;

class LessonPolicy
{
    /**
     * Only instructor and tenant admin can create lesson
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['instructor', 'tenant_admin']);
    }

    /**
     * Only lesson owner (via course) or tenant admin can update
     */
    public function update(User $user, Lesson $lesson): bool
    {
        $course = $lesson->section->course;

        return ($user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id) ||
               ($user->hasRole('tenant_admin') &&
                $user->tenant_id === $course->tenant_id);
    }

    /**
     * Only lesson owner (via course) or tenant admin can delete
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        $course = $lesson->section->course;

        return ($user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id) ||
               ($user->hasRole('tenant_admin') &&
                $user->tenant_id === $course->tenant_id);
    }

    /**
     * Anyone in same tenant can view lesson
     */
    public function view(User $user, Lesson $lesson): bool
    {
        $course = $lesson->section->course;
        return $user->tenant_id === $course->tenant_id;
    }
}
