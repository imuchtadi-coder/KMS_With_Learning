<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoToHLS;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Video;
use App\Traits\HasTenantStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    use HasTenantStorage;

    /**
     * Store uploaded video for a lesson
     */
    public function store(Request $request, Lesson $lesson)
    {
        $course = $lesson->section->course;
        Gate::forUser($request->user())->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi,mkv,webm|max:2147483648',
        ]);

        $tenantId = $course->tenant_id;
        $file = $request->file('video');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs(
            self::tenantPath('videos/uploads', $tenantId),
            $filename
        );

        $video = Video::create([
            'lesson_id' => $lesson->id,
            'tenant_id' => $tenantId,
            'title' => $validated['title'],
            'original_path' => $path,
            'status' => 'pending',
        ]);

        ProcessVideoToHLS::dispatch($video);

        Log::info('Video uploaded and queued for processing', [
            'video_id' => $video->id,
            'lesson_id' => $lesson->id,
        ]);

        return redirect()->back()->with('success', 'Video uploaded! Processing started. This may take a few minutes.');
    }

    /**
     * Upload a video through the JSON API.
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'video' => ['required', 'file', 'mimes:mp4,mov,avi,mkv,webm', 'max:2097152'],
        ]);

        $lesson = Lesson::with('section.course')->findOrFail($validated['lesson_id']);
        $course = $lesson->section->course;

        Gate::forUser($request->user())->authorize('update', $course);

        $file = $request->file('video');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs(
            self::tenantPath('videos/uploads', $course->tenant_id),
            $filename
        );

        $video = Video::create([
            'lesson_id' => $lesson->id,
            'tenant_id' => $course->tenant_id,
            'title' => $validated['title'],
            'original_path' => $path,
            'status' => 'pending',
        ]);

        ProcessVideoToHLS::dispatch($video);

        return response()->json([
            'message' => 'Video uploaded and queued for processing.',
            'video' => [
                'id' => $video->id,
                'lesson_id' => $video->lesson_id,
                'title' => $video->title,
                'status' => $video->status,
            ],
        ], 202);
    }

    /**
     * Return an authorized playback URL.
     */
    public function play(Request $request, Video $video)
    {
        $course = $video->lesson->section->course;

        Gate::forUser($request->user())->authorize('view', $course);
        $this->ensureCanConsumeCourse($request, $course);

        abort_unless($video->status === 'completed' && $video->hls_url, 409, 'Video is not ready for playback.');

        return response()->json([
            'id' => $video->id,
            'title' => $video->title,
            'url' => $video->hls_url,
            'duration_seconds' => $video->duration_seconds,
        ]);
    }

    /**
     * Delete video
     */
    public function destroy(Video $video)
    {
        $course = $video->lesson->section->course;
        Gate::forUser(request()->user())->authorize('update', $course);

        if ($video->original_path && Storage::exists($video->original_path)) {
            Storage::delete($video->original_path);
        }

        if ($video->hls_path && Storage::exists($video->hls_path)) {
            Storage::deleteDirectory($video->hls_path);
        }

        $video->delete();

        Log::info('Video deleted', ['video_id' => $video->id]);

        return redirect()->back()->with('success', 'Video deleted successfully!');
    }

    /**
     * Get video status (for AJAX checks)
     */
    public function status(Request $request, Video $video)
    {
        $course = $video->lesson->section->course;

        Gate::forUser($request->user())->authorize('update', $course);

        $response = [
            'id' => $video->id,
            'status' => $video->status,
            'duration_seconds' => $video->duration_seconds,
        ];

        if ($video->status === 'failed') {
            $response['message'] = 'Video processing failed. Check the application logs for details.';
        }

        return response()->json($response);
    }

    /**
     * Get videos for a lesson (for AJAX requests)
     */
    public function getForLesson(Lesson $lesson)
    {
        $course = $lesson->section->course;

        Gate::forUser(request()->user())->authorize('update', $course);

        $videos = $lesson->videos()
            ->select('id', 'lesson_id', 'title', 'duration_seconds', 'status')
            ->get();

        return response()->json($videos);
    }

    private function ensureCanConsumeCourse(Request $request, Course $course): void
    {
        $user = $request->user();

        if ($user->id === $course->created_by || $user->hasRole('tenant_admin')) {
            return;
        }

        abort_unless(
            $course->enrollments()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists(),
            403
        );
    }
}
