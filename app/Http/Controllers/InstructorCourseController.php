<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\HasTenantStorage;
use Inertia\Inertia;
use App\Models\Certificate;

class InstructorCourseController extends Controller
{
    use HasTenantStorage;

    public function dashboard()
    {
        $user = auth()->user();
        $totalCourses = Course::where('created_by', $user->id)->count();
        $publishedCourses = Course::where('created_by', $user->id)->where('published', true)->count();
        $draftCourses = Course::where('created_by', $user->id)->where('published', false)->count();
        $totalStudents = Enrollment::whereHas('course', fn($q) => $q->where('created_by', $user->id))->distinct('user_id')->count('user_id');

        $recentCourses = Course::where('created_by', $user->id)->with('tenant')->latest()->paginate(6)->withQueryString();

        return Inertia::render('Instructor/Dashboard', compact('totalCourses', 'publishedCourses', 'draftCourses', 'totalStudents', 'recentCourses'));
    }

    public function index()
    {
        $user = auth()->user();
        $courses = Course::where('created_by', $user->id)
            ->with(['tenant', 'sections.lessons'])
            ->withCount(['quizzes', 'assignments', 'enrollments'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Course::where('created_by', $user->id)->count(),
            'published' => Course::where('created_by', $user->id)->where('published', true)->count(),
            'draft' => Course::where('created_by', $user->id)->where('published', false)->count(),
            'total_students' => Enrollment::whereHas('course', fn($q) => $q->where('created_by', $user->id))->count(),
        ];

        return Inertia::render('Instructor/Courses/Index', compact('courses', 'stats'));
    }

    public function create()
    {
        return Inertia::render('Instructor/Courses/Create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $course = Course::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'],
            'level' => $validated['level'],
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'published' => false,
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store($this->tenantPath('courses'), 'public');
            $course->update(['thumbnail' => Storage::url($path)]);
        }

        if ($request->filled('category_id')) $course->update(['category_id' => $request->category_id]);
        if ($request->filled('tags')) $course->tags()->sync($request->tags);

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Course created successfully!');
    }

    public function edit($courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $course->load(['sections.lessons.videos', 'sections.lessons.materials', 'tags']);

        $quizzes = Quiz::where('course_id', $course->id)->with('section')->get();
        $assignments = Assignment::where('course_id', $course->id)->with('section')->get();

        return Inertia::render('Instructor/Courses/Edit', compact('course', 'quizzes', 'assignments'));
    }

    public function update(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'], 'tags.*' => ['exists:tags,id'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $course->update($validated);

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                $oldPath = str_replace(Storage::url(''), '', $course->thumbnail);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('thumbnail')->store($this->tenantPath('courses'), 'public');
            $course->update(['thumbnail' => Storage::url($path)]);
        }

        if ($request->has('tags')) $course->tags()->sync($request->tags ?? []);

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Course updated successfully!');
    }

    public function destroy($courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $course->delete();

        return redirect()->route('instructor.courses.index')->with('success', 'Course deleted successfully!');
    }

    public function storeSection(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $validated = $request->validate(['title' => ['required', 'string', 'max:255']]);
        $section = Section::create(['course_id' => $course->id, 'title' => $validated['title'], 'order' => Section::where('course_id', $course->id)->count() + 1]);

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Section created!');
    }

    public function updateSection(Request $request, Section $section)
    {
        $user = auth()->user();
        $course = $section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $validated = $request->validate(['title' => ['required', 'string', 'max:255']]);
        $section->update($validated);

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Section updated!');
    }

    public function destroySection(Section $section)
    {
        $user = auth()->user();
        $course = $section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $section->delete();

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Section deleted!');
    }

    public function storeLesson(Request $request, Section $section)
    {
        $user = auth()->user();
        $course = $section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $validated = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string']]);
        Lesson::create(['section_id' => $section->id, 'title' => $validated['title'], 'description' => $validated['description'] ?? null, 'order' => Lesson::where('section_id', $section->id)->count() + 1]);

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Lesson created!');
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $user = auth()->user();
        $course = $lesson->section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $validated = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string']]);
        $lesson->update($validated);

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Lesson updated!');
    }

    public function destroyLesson(Lesson $lesson)
    {
        $user = auth()->user();
        $course = $lesson->section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $lesson->delete();

        return redirect()->route('instructor.courses.edit', $course)->with('success', 'Lesson deleted!');
    }

    public function show($courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $course->load(['sections.lessons.videos', 'sections.lessons.materials', 'instructor', 'enrollments', 'quizzes']);

        return Inertia::render('Instructor/Courses/Show', compact('course'));
    }

    public function preview($courseId)
    {
        $course = Course::findOrFail($courseId);
        $user = auth()->user();
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) && !$user->hasRole('tenant_admin')) abort(403);

        $course->load(['instructor', 'sections.lessons.videos', 'sections.lessons.materials', 'enrollments']);
        $isEnrolled = $course->enrollments()->where('user_id', $user->id)->exists();

        return Inertia::render('Instructor/Courses/Preview', compact('course', 'isEnrolled'));
    }

    public function progress($courseId)
    {
        $course = Course::findOrFail($courseId);

        $user = auth()->user();

        if (
            !(
                $user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id
            ) &&
            !$user->hasRole('tenant_admin')
        ) {
            abort(403);
        }

        $course->load([
            'sections.lessons',
            'enrollments.user',
        ]);

        $allLessonIds = $course->sections
            ->flatMap->lessons
            ->pluck('id');

        $totalLessons = $allLessonIds->count();

        $enrollments = $course->enrollments->map(function ($enrollment) use (
            $allLessonIds,
            $totalLessons
        ) {
            $completedCount = Progress::where('user_id', $enrollment->user_id)
                ->whereIn('lesson_id', $allLessonIds)
                ->where('completed', true)
                ->count();

            $certificate = Certificate::where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->first();

            $enrollment->completed_lessons = $completedCount;
            $enrollment->total_lessons = $totalLessons;

            $enrollment->progress_percentage = $totalLessons > 0
                ? round(($completedCount / $totalLessons) * 100)
                : 0;

            $enrollment->is_completed =
                $enrollment->status === 'completed' ||
                $enrollment->completed_at !== null;

            $enrollment->has_certificate = $certificate !== null;

            $enrollment->certificate = $certificate;

            return $enrollment;
        });

        return Inertia::render(
            'Instructor/Courses/Progress',
            compact(
                'course',
                'enrollments',
                'totalLessons'
            )
        );
    }

    public function approveCertificate($courseId, $enrollmentId)
    {
        $course = Course::findOrFail($courseId);

        $user = auth()->user();

        /*
        * =========================================================
        * AUTHORIZATION
        * =========================================================
        */
        if (
            !(
                $user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id
            ) &&
            !$user->hasRole('tenant_admin')
        ) {
            abort(403);
        }

        /*
        * =========================================================
        * GET ENROLLMENT
        * =========================================================
        */
        $enrollment = Enrollment::with('user')
            ->where('id', $enrollmentId)
            ->where('course_id', $course->id)
            ->firstOrFail();

        /*
        * =========================================================
        * CHECK COURSE COMPLETION
        * =========================================================
        */
        if (
            $enrollment->status !== 'completed' &&
            $enrollment->completed_at === null
        ) {
            return back()->with(
                'error',
                'Student has not completed this course.'
            );
        }

        /*
        * =========================================================
        * CHECK EXISTING CERTIFICATE
        * =========================================================
        */
        $existingCertificate = Certificate::where('user_id', $enrollment->user_id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return back()->with(
                'success',
                'Certificate has already been issued.'
            );
        }

        /*
        * =========================================================
        * CREATE CERTIFICATE
        * =========================================================
        */
        $certificate = Certificate::create([
            'user_id' => $enrollment->user_id,
            'course_id' => $course->id,
            'certificate_number' => Certificate::generateCertificateNumber(),
            'issued_at' => now(),
        ]);

        /*
        * =========================================================
        * GENERATE PDF
        * =========================================================
        */
        CertificateController::generatePDF($certificate);

        return back()->with(
            'success',
            'Certificate issued successfully.'
        );
    }

    public function reorderLessons(Request $request, Section $section)
    {
        $user = auth()->user();
        $course = $section->course;

        // Authorization
        if (
            !(
                $user->id === $course->created_by &&
                $user->tenant_id === $course->tenant_id
            ) &&
            !$user->hasRole('tenant_admin')
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'lessons' => ['required', 'array', 'min:1'],
            'lessons.*' => ['required', 'integer', 'distinct'],
        ]);

        $lessonIds = collect($validated['lessons'])
            ->map(fn ($id) => (int) $id)
            ->values();

        // Ambil semua lesson yang memang berada di section ini
        $sectionLessonIds = Lesson::where('section_id', $section->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        // Pastikan lesson yang dikirim lengkap dan hanya berasal dari section ini
        if (
            $lessonIds->count() !== $sectionLessonIds->count() ||
            $lessonIds->sort()->values()->all() !== $sectionLessonIds->sort()->values()->all()
        ) {
            return back()->with('error', 'Invalid lesson order.');
        }

        DB::transaction(function () use ($lessonIds, $section) {

            foreach ($lessonIds as $index => $lessonId) {
                Lesson::where('id', $lessonId)
                    ->where('section_id', $section->id)
                    ->update([
                        'order' => $index + 1,
                    ]);
            }
        });

        return back()->with('success', 'Lesson order updated successfully.');
    }
}
