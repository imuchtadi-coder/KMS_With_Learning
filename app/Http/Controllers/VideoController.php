<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoToHLS;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Video;
use App\Support\YouTubeUrl;
use App\Traits\HasTenantStorage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

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

        $validated = $this->validateVideoRequest($request);
        $video = $this->createVideo($lesson, $validated, $request);

        Log::info('Video material created', [
            'video_id' => $video->id,
            'lesson_id' => $lesson->id,
            'source_type' => $video->source_type,
        ]);

        $message = $video->isYoutube()
            ? 'Tautan YouTube berhasil ditambahkan.'
            : 'Video berhasil diunggah dan sedang diproses.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Upload a video through the JSON API.
     */
    public function upload(Request $request)
    {
        $validated = $this->validateVideoRequest($request, true);

        $lesson = Lesson::with('section.course')->findOrFail($validated['lesson_id']);
        $course = $lesson->section->course;

        Gate::forUser($request->user())->authorize('update', $course);

        $video = $this->createVideo($lesson, $validated, $request);

        return response()->json([
            'message' => $video->isYoutube()
                ? 'YouTube video added.'
                : 'Video uploaded and queued for processing.',
            'video' => [
                'id' => $video->id,
                'lesson_id' => $video->lesson_id,
                'title' => $video->title,
                'source_type' => $video->source_type,
                'status' => $video->status,
            ],
        ], $video->isYoutube() ? 201 : 202);
    }

    /**
     * Return an authorized playback URL.
     */
    public function play(Request $request, Video $video)
    {
        $course = $video->lesson->section->course;

        Gate::forUser($request->user())->authorize('view', $course);
        $this->ensureCanConsumeCourse($request, $course);

        abort_unless($video->status === 'ready', 409, 'Video is not ready for playback.');

        $playbackUrl = $video->isYoutube()
            ? YouTubeUrl::embedUrl($video->youtube_video_id)
            : $video->hls_url;

        abort_unless($playbackUrl, 409, 'Video is not ready for playback.');

        return response()->json([
            'id' => $video->id,
            'title' => $video->title,
            'source_type' => $video->source_type ?? Video::SOURCE_HLS,
            'url' => $playbackUrl,
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

        if ($video->isHls()) {
            if ($video->original_path && Storage::exists($video->original_path)) {
                Storage::delete($video->original_path);
            }

            if ($video->hls_path && Storage::disk('public')->exists($video->hls_path)) {
                Storage::disk('public')->deleteDirectory($video->hls_path);
            }
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
            'source_type' => $video->source_type ?? Video::SOURCE_HLS,
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
            ->select('id', 'lesson_id', 'title', 'source_type', 'youtube_video_id', 'duration_seconds', 'status')
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

    private function validateVideoRequest(Request $request, bool $includeLesson = false): array
    {
        $request->merge([
            'source_type' => $request->input('source_type', Video::SOURCE_HLS),
        ]);

        $rules = [
            'source_type' => ['required', Rule::in(Video::SOURCE_TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'video' => ['required_if:source_type,'.Video::SOURCE_HLS, 'nullable', 'file', 'mimes:mp4,mov,avi,mkv,webm', 'max:2097152'],
            'youtube_url' => [
                'required_if:source_type,'.Video::SOURCE_YOUTUBE,
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                    if ($request->input('source_type') !== Video::SOURCE_YOUTUBE || ! is_string($value)) {
                        return;
                    }

                    try {
                        YouTubeUrl::videoId($value);
                    } catch (InvalidArgumentException $exception) {
                        $fail($exception->getMessage());
                    }
                },
            ],
        ];

        if ($includeLesson) {
            $rules['lesson_id'] = ['required', 'integer', 'exists:lessons,id'];
        }

        return $request->validate($rules);
    }

    private function createVideo(Lesson $lesson, array $validated, Request $request): Video
    {
        $course = $lesson->section->course;

        if ($validated['source_type'] === Video::SOURCE_YOUTUBE) {
            $youtubeUrl = trim($validated['youtube_url']);

            return Video::create([
                'lesson_id' => $lesson->id,
                'tenant_id' => $course->tenant_id,
                'title' => $validated['title'],
                'source_type' => Video::SOURCE_YOUTUBE,
                'youtube_video_id' => YouTubeUrl::videoId($youtubeUrl),
                'youtube_url' => $youtubeUrl,
                'status' => 'ready',
            ]);
        }

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
            'source_type' => Video::SOURCE_HLS,
            'original_path' => $path,
            'status' => 'pending',
        ]);

        ProcessVideoToHLS::dispatch($video);

        return $video;
    }
}
