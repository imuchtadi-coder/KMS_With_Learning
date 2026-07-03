<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['instructor', 'tenant_admin', 'superadmin', 'student']);
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('superadmin')) return true;
        return $user->tenant_id === $quiz->course->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['instructor', 'tenant_admin']);
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('superadmin')) return true;
        return ($user->id === $quiz->created_by && $user->tenant_id === $quiz->course->tenant_id)
            || ($user->hasRole('tenant_admin') && $user->tenant_id === $quiz->course->tenant_id);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    public function publish(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('superadmin')) return true;
        return $user->hasRole('tenant_admin') && $user->tenant_id === $quiz->course->tenant_id;
    }

    public function take(User $user, Quiz $quiz): bool
    {
        if (!$quiz->published) return false;
        return $user->hasRole('student') && $user->tenant_id === $quiz->course->tenant_id;
    }
}
