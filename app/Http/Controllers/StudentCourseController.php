<?php

namespace App\Http\Controllers;

use App\Http\Resources\Academy\AcademyCourseDetailResource;
use App\Http\Resources\Academy\AcademyCourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Tag;
// use App\Notifications\CourseCompleted;
use App\Services\GamificationService;
use App\Notifications\EnrollmentConfirmation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CourseCompletionService;

class StudentCourseController extends Controller
{
    /**
     * Student Dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()->with('course.sections.lessons', 'course.instructor', 'course.reviews', 'course.tags', 'course.category')->get();
        $totalEnrolled = $enrollments->count();
        $inProgress = $enrollments->whereNull('completed_at')->count();
        $completed = $enrollments->whereNotNull('completed_at')->count();

        // Transform enrollments with AcademyCourseResource
        $enrollments = $enrollments->map(function ($enrollment) {
            $resource = new AcademyCourseResource($enrollment->course);
            $resource->forUser($enrollment->user_id);

            return $resource->toArray(request());
        });

        $recentEnrollments = array_slice($enrollments->toArray(), 0, 6);

        // Get certificates
        $certificates = $user->certificates()->with('course')->get();

        return inertia('Student/Dashboard', [
            'recentEnrollments' => $recentEnrollments,
            'totalEnrolled' => $totalEnrolled,
            'inProgress' => $inProgress,
            'completed' => $completed,
            'certificates' => $certificates,
            'tenantName' => $user->tenant?->name ?? 'Platform',
        ]);
    }

    /**
     * List all available courses
     */
    public function listCourses(Request $request)
    {
        $user = auth()->user();
        $query = Course::where('published', true)
            ->where('tenant_id', $user->tenant_id)
            ->with(['instructor', 'sections', 'category', 'tags'])
            ->withCount(['enrollments', 'reviews']);

        // Filter by search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by tag
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        $courses = $query->paginate(12);

        // Track enrollment status and ids per course
        $enrollments = Enrollment::where('user_id', $user->id)
            ->select('id', 'course_id')
            ->get()
            ->keyBy('course_id');

        // Transform using AcademyCourseResource
        $courses->getCollection()->transform(function ($course) use ($enrollments, $user) {
            $course->enrolled = $enrollments->has($course->id);
            $resource = new AcademyCourseResource($course);
            $resource->forUser($user->id);

            $data = $resource->toArray(request());
            $data['enrollment_id'] = $enrollments->get($course->id)?->id;

            return $data;
        });

        $categories = Category::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        $tags = Tag::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        return inertia('Student/Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'tags' => $tags,
            'tenantName' => $user->tenant?->name ?? 'Platform',
            'filters' => $request->only(['search', 'level', 'category_id', 'tag_id']),
        ]);
    }

    /**
     * Show course detail
     */
    public function showCourse(string $course)
    {
        $user = auth()->user();

        $course = is_numeric($course)
            ? Course::findOrFail($course)
            : Course::where('slug', $course)->firstOrFail();

        // Only show published courses in same tenant
        if (!$course->published || $course->tenant_id !== $user->tenant_id) {
            abort(404);
        }

        $course->load(['instructor', 'sections.lessons', 'enrollments', 'reviews.user', 'tags', 'category'])
            ->loadCount(['enrollments', 'reviews']);

        $isEnrolled = $course->enrollments()->where('user_id', $user->id)->exists();
        $enrollmentId = $isEnrolled
            ? Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->value('id')
            : null;
        $userReview = $course->reviews->where('user_id', $user->id)->first();

        $resource = new AcademyCourseDetailResource($course);
        $resource->forUser($user->id);

        return inertia('Student/Courses/Show', [
            'course' => $resource->toArray(request()),
            'isEnrolled' => $isEnrolled,
            'enrollmentId' => $enrollmentId,
            'userReview' => $userReview,
        ]);
    }

    /**
     * Enroll in course
     */
    public function enroll(Request $request, Course $course)
    {
        $user = auth()->user();

        \Log::info('HIT_STUDENT_COURSE_ENROLL', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'tenant_initialized' => tenancy()->initialized,
            'tenant_id' => tenant('id'),
        ]);

        // Keep enrollment and its queued notification inside the student's tenant.
        if (
            !tenancy()->initialized
            || (string) $course->tenant_id !== (string) $user->tenant_id
            || (string) tenant('id') !== (string) $user->tenant_id
        ) {
            abort(404);
        }

        // Check if already enrolled
        if ($course->enrollments()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }

        // Only enroll in published courses
        if (!$course->published) {
            return redirect()->back()->with('error', 'Course not available.');
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        \Log::info('BEFORE EnrollmentConfirmation notify', [
            'tenant_initialized' => tenancy()->initialized,
            'tenant_id' => tenant('id'),
            'tenant_key' => tenancy()->initialized
                ? tenant()->getTenantKey()
                : null,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $notification = new EnrollmentConfirmation($course, (int) $enrollment->id);

        try {
            // Persist the in-app notification before the redirect so the shared
            // navbar props include it on the next page response.
            $user->notifyNow($notification, ['database']);
        } catch (\Throwable $exception) {
            \Log::warning('Enrollment database notification could not be stored', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'exception' => $exception::class,
            ]);
        }

        // SMTP remains queued and tenant-aware; only the in-app channel is immediate.
        $user->notify($notification->onlyMail());

        return redirect()->route('student.courses.show', $course)
                       ->with('success', 'Enrolled successfully!');
    }

    /**
     * My enrollments
     */
    public function myEnrollments()
    {
        $user = auth()->user();

        $withCourse = fn($q) => $q->with([
            'instructor', 'sections.lessons', 'reviews', 'tags', 'category',
        ])->withCount('enrollments');

        $enrollments = $user->enrollments()
            ->with(['course' => $withCourse])
            ->get()
            ->map(function ($enrollment) {
                $resource = new AcademyCourseResource($enrollment->course);
                $resource->forUser($enrollment->user_id);

                $data = $resource->toArray(request());
                $data['enrollment_id'] = $enrollment->id;

                return $data;
            })->values();

        return inertia('Student/Enrollments/Index', [
            'courses' => $enrollments,
        ]);
    }

    /**
     * Show enrollment detail
     */
    public function showEnrollment($enrollmentId)
    {
        $user = auth()->user();

        $enrollment = Enrollment::where('user_id', $user->id)->find($enrollmentId);

        if (!$enrollment) {
            return redirect()->route('student.enrollments')
                ->with('error', 'Enrollment not found.');
        }

        $enrollment->load([
            'course.instructor',
            'course.sections.lessons.videos',
            'course.sections.lessons.progresses' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            },
        ]);

        // Calculate progress
        $course = $enrollment->course;
        $allLessons = $course->sections->flatMap->lessons;
        $totalLessons = $allLessons->count();
        $completedLessons = Progress::where('user_id', $user->id)
            ->whereIn('lesson_id', $allLessons->pluck('id'))
            ->where('completed', true)
            ->count();
        $courseProgress = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100)
            : 0;

        // Update enrollment progress
        $enrollment->progress = $courseProgress;

        // Get course assessments
        $courseQuizzes = Quiz::where('course_id', $enrollment->course_id)->where('published', true)->get();
        $courseAssignments = Assignment::where('course_id', $enrollment->course_id)->get();

        return Inertia::render('Student/Enrollments/Show', compact('enrollment', 'totalLessons', 'completedLessons', 'courseProgress', 'courseQuizzes', 'courseAssignments'));
    }

    /**
     * View lesson
     */
    public function viewLesson(Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        // Check enrollment
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            abort(403, 'Not enrolled in this course');
        }

        // Check lesson belongs to course
        if ($lesson->section->course_id !== $course->id) {
            abort(404, 'Lesson not found in this course');
        }

        $lesson->load(['videos', 'section', 'materials']);

        // Load sections with lessons and per-lesson progress
        $course->load(['sections' => function ($q) use ($user) {
            $q->with(['lessons' => function ($q) use ($user) {
                $q->with(['progresses' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }])->orderBy('order');
            }])->orderBy('order');
        }]);

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();

        // Get progress for this lesson
        $progress = Progress::where('user_id', $user->id)
                           ->where('lesson_id', $lesson->id)
                           ->first();

        // Calculate course progress
        $allLessons = $course->sections->flatMap->lessons;
        $totalLessons = $allLessons->count();
        $completedLessons = Progress::where('user_id', $user->id)
                                    ->whereIn('lesson_id', $allLessons->pluck('id'))
                                    ->where('completed', true)
                                    ->count();
        $courseProgress = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100)
            : 0;

        // Get previous and next lesson
        $allLessonIds = $allLessons->pluck('id')->values();
        $currentIndex = $allLessonIds->search($lesson->id);
        $previousLesson = $currentIndex > 0 ? Lesson::find($allLessonIds[$currentIndex - 1]) : null;
        $nextLesson = $currentIndex < count($allLessonIds) - 1 ? Lesson::find($allLessonIds[$currentIndex + 1]) : null;

        return inertia('Student/Lessons/Show', compact(
            'course', 'lesson', 'enrollment', 'progress',
            'courseProgress', 'previousLesson', 'nextLesson'
        ));
    }

    public function __construct(
        protected CourseCompletionService $courseCompletionService
    ) {
    }

    /**
     * Mark lesson as complete
     */
    public function completeLesson(Request $request, Lesson $lesson)
    {
        $user = auth()->user();

        /*
        * =========================================================
        * CHECK ENROLLMENT
        * =========================================================
        */
        $course = $lesson->section->course;

        if (
            ! $course->enrollments()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists()
        ) {
            abort(403, 'Not enrolled in this course');
        }

        /*
        * =========================================================
        * COMPLETE LESSON
        * =========================================================
        */
        Progress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'percentage' => 100,
                'completed' => true,
                'completed_at' => now(),
            ]
        );

        /*
        * =========================================================
        * COURSE COMPLETION
        * =========================================================
        *
        * CourseCompletionService bertanggung jawab untuk:
        * - cek seluruh lesson
        * - mengubah enrollment menjadi completed
        * - completed_at
        * - notification
        * - course completion points
        * - badges
        *
        * Certificate TIDAK dibuat di sini.
        * Certificate hanya dibuat setelah instructor approve.
        */
        $this->courseCompletionService
            ->completeEnrollmentIfEligible(
                $course,
                $user->id
            );

        /*
        * =========================================================
        * LESSON COMPLETION GAMIFICATION
        * =========================================================
        */
        app(GamificationService::class)->awardPoints(
            $user,
            'lesson_completed',
            $course->id
        );

        app(GamificationService::class)->updateStreak($user);

        app(GamificationService::class)->checkBadges($user);

        return redirect()
            ->back()
            ->with('success', 'Lesson marked as complete!');
    }

}
