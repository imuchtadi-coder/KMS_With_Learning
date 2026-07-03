<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Section;

class SectionPolicy
{
    /**
     * Only instructor and tenant admin can create section
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['instructor', 'tenant_admin']);
    }

    /**
     * Only section owner (via course) or tenant admin can update
     */
    public function update(User $user, Section $section): bool
    {
        $course = $section->course;

        return ($user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id) ||
               ($user->hasRole('tenant_admin') &&
                $user->tenant_id === $course->tenant_id);
    }

    /**
     * Only section owner (via course) or tenant admin can delete
     */
    public function delete(User $user, Section $section): bool
    {
        $course = $section->course;

        return ($user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id) ||
               ($user->hasRole('tenant_admin') &&
                $user->tenant_id === $course->tenant_id);
    }

    /**
     * Anyone in same tenant can view section
     */
    public function view(User $user, Section $section): bool
    {
        $course = $section->course;
        return $user->tenant_id === $course->tenant_id;
    }
}
