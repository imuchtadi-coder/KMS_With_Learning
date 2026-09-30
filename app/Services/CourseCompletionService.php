<?php

namespace App\Services;

// use App\Http\Controllers\CertificateController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\User;
use App\Notifications\CourseCompleted;

class CourseCompletionService
{
    /**
     * Check whether all lessons are completed and finalize the enrollment.
     *
     * @return bool
     *         true  = course baru saja diselesaikan
     *         false = belum semua lesson selesai / course sudah pernah selesai
     */
    public function completeEnrollmentIfEligible(
        Course $course,
        int $userId
    ): bool {
        /*
         * =========================================================
         * 1. AMBIL SELURUH LESSON DALAM COURSE
         * =========================================================
         */
        $lessonIds = Lesson::query()
            ->whereHas('section', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->pluck('id');

        $totalLessons = $lessonIds->count();

        /*
         * Course tanpa lesson tidak dianggap completed.
         */
        if ($totalLessons === 0) {
            return false;
        }

        /*
         * =========================================================
         * 2. CEK LESSON YANG SUDAH COMPLETED
         * =========================================================
         */
        $completedLessons = Progress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->count();

        /*
         * Belum semua lesson selesai.
         */
        if ($completedLessons !== $totalLessons) {
            return false;
        }

        /*
         * =========================================================
         * 3. ATOMIC COURSE COMPLETION
         * =========================================================
         *
         * Hanya enrollment yang masih memiliki
         * completed_at = NULL yang boleh diubah.
         *
         * Request pertama:
         *
         * NULL
         *   ↓
         * timestamp
         *
         * menghasilkan affected rows = 1.
         *
         * Request berikutnya:
         *
         * timestamp
         *   ↓
         * tidak memenuhi whereNull()
         *
         * menghasilkan affected rows = 0.
         */
        $completedNow = Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->whereNull('completed_at')
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]) > 0;

        /*
         * Course sudah pernah diselesaikan.
         *
         * Jangan jalankan side effect lagi.
         */
        if (! $completedNow) {
            return false;
        }

        /*
         * =========================================================
         * 4. COURSE COMPLETION SIDE EFFECTS
         * =========================================================
         *
         * Hanya request yang berhasil mengubah
         * completed_at NULL -> timestamp yang boleh
         * menjalankan seluruh side effect.
         */

        // /*
        //  * Certificate trigger ini dimatikan, untuktidak dibuat otomatis
        //  */
        // CertificateController::generateIfEligible(
        //     $userId,
        //     $course->id
        // );

        /*
         * Notification
         */
        $user = User::findOrFail($userId);

        $user->notify(
            new CourseCompleted($course)
        );

        /*
         * Gamification
         */
        app(GamificationService::class)->awardPoints(
            $user,
            'course_completed',
            $course->id
        );

        app(GamificationService::class)->checkBadges(
            $user
        );

        return true;
    }
}