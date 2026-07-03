<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoToHLS;
use App\Models\Video;
use App\Models\Lesson;
use App\Traits\HasTenantStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    use HasTenantStorage;


    /**
     * Store uploaded video for a lesson
     */
    public function store(Request $request, Lesson $lesson)
    {
        $user = auth()->user();

        $course = $lesson->section->course;
        if ($user->id !== $course->created_by && !$user->hasRole('tenant_admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi,mkv,webm|max:2147483648',
        ]);

        $tenantId = $course->tenant_id;
        $file = $request->file('video');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

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
            'lesson_id' => $lesson->id
        ]);

        return redirect()->back()->with('success', 'Video uploaded! Processing started. This may take a few minutes.');
    }

    /**
     * Delete video
     */
    public function destroy(Video $video)
    {
        $user = auth()->user();

        $course = $video->lesson->section->course;
        if ($user->id !== $course->created_by && !$user->hasRole('tenant_admin')) {
            abort(403);
        }

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
    public function status(Video $video)
    {
        return response()->json([
            'id' => $video->id,
            'status' => $video->status,
            'hls_path' => $video->hls_path,
            'duration_seconds' => $video->duration_seconds,
            'error_message' => $video->error_message,
        ]);
    }

    /**
     * Get videos for a lesson (for AJAX requests)
     */
    public function getForLesson(Lesson $lesson)
    {
        $user = auth()->user();
        $course = $lesson->section->course;

        // Check authorization - course creator, tenant admin, or enrolled student
        $isInstructor = $course->created_by === $user->id;
        $isTenantAdmin = $user->hasRole('tenant_admin');
        $isEnrolled = $course->enrollments()->where('user_id', $user->id)->exists();

        if (!$isInstructor && !$isTenantAdmin && !$isEnrolled) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $videos = $lesson->videos()->select('id', 'lesson_id', 'title', 'hls_path', 'duration_seconds', 'status')->get();
        return response()->json($videos);
    }
}
