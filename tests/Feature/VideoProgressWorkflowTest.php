<?php

namespace Tests\Feature;

use App\Http\Controllers\ProgressController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Section;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VideoProgressWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_web_and_api_progress_routes_use_the_same_controller(): void
    {
        $this->assertSame(
            ProgressController::class.'@update',
            Route::getRoutes()->getByName('student.lessons.update-progress')->getActionName()
        );
        $this->assertSame(
            ProgressController::class.'@update',
            Route::getRoutes()->match(request()->create('/api/progress', 'POST'))->getActionName()
        );
    }

    public function test_progress_is_monotonic_bounded_and_completed_at_eighty_percent(): void
    {
        [$student, $lesson, $video] = $this->progressContext(Video::SOURCE_HLS, 100);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 60,
            'total_seconds' => 999,
        ])->assertOk()->assertJsonPath('progress.completed', false);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 20,
            'total_seconds' => 20,
        ])->assertOk()->assertJsonPath('progress.watched_seconds', 60);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 120,
            'total_seconds' => 120,
        ])->assertOk()
            ->assertJsonPath('progress.watched_seconds', 100)
            ->assertJsonPath('progress.total_seconds', 100)
            ->assertJsonPath('progress.completed', true);

        $progress = Progress::query()->sole();
        $completedAt = $progress->completed_at;

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 10,
            'total_seconds' => 100,
        ])->assertOk()->assertJsonPath('progress.completed', true);

        $progress->refresh();
        $this->assertSame(100, $progress->watched_seconds);
        $this->assertTrue($progress->completed);
        $this->assertTrue($completedAt->equalTo($progress->completed_at));
    }

    public function test_first_youtube_player_duration_is_persisted_as_server_duration(): void
    {
        [$student, $lesson, $video] = $this->progressContext(Video::SOURCE_YOUTUBE);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 160,
            'total_seconds' => 200,
        ])->assertOk()
            ->assertJsonPath('progress.total_seconds', 200)
            ->assertJsonPath('progress.completed', true);

        $this->assertSame(200, $video->fresh()->duration_seconds);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 250,
            'total_seconds' => 300,
        ])->assertOk()
            ->assertJsonPath('progress.watched_seconds', 200)
            ->assertJsonPath('progress.total_seconds', 200);

        $this->assertSame(200, $video->fresh()->duration_seconds);
    }

    public function test_inactive_enrollment_cannot_update_progress(): void
    {
        [$student, $lesson, $video, $enrollment] = $this->progressContext(Video::SOURCE_HLS, 100);
        $enrollment->update(['status' => 'cancelled']);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $lesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 10,
            'total_seconds' => 100,
        ])->assertForbidden();

        $this->assertDatabaseCount('progress', 0);
    }

    public function test_video_must_belong_to_the_submitted_lesson(): void
    {
        [$student, $lesson, $video] = $this->progressContext(Video::SOURCE_HLS, 100);
        $otherLesson = Lesson::create([
            'section_id' => $lesson->section_id,
            'title' => 'Pelajaran lain',
        ]);

        $this->actingAs($student)->postJson(route('student.lessons.update-progress'), [
            'lesson_id' => $otherLesson->id,
            'video_id' => $video->id,
            'watched_seconds' => 10,
            'total_seconds' => 100,
        ])->assertUnprocessable()->assertJsonValidationErrors('video_id');

        $this->assertDatabaseCount('progress', 0);
    }

    private function progressContext(string $sourceType, ?int $duration = null): array
    {
        Tenant::create(['id' => 'tenant-progress']);
        $instructor = User::factory()->create(['tenant_id' => 'tenant-progress']);
        $student = User::factory()->create(['tenant_id' => 'tenant-progress']);
        $student->assignRole('student');
        $course = Course::create([
            'tenant_id' => 'tenant-progress',
            'created_by' => $instructor->id,
            'title' => 'Kursus progres',
            'slug' => 'kursus-progres',
            'published' => true,
        ]);
        $section = Section::create([
            'course_id' => $course->id,
            'title' => 'Bagian progres',
        ]);
        $lesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Pelajaran progres',
        ]);
        $video = Video::create([
            'lesson_id' => $lesson->id,
            'tenant_id' => 'tenant-progress',
            'title' => 'Video progres',
            'source_type' => $sourceType,
            'youtube_video_id' => $sourceType === Video::SOURCE_YOUTUBE ? 'dQw4w9WgXcQ' : null,
            'youtube_url' => $sourceType === Video::SOURCE_YOUTUBE ? 'https://youtu.be/dQw4w9WgXcQ' : null,
            'hls_path' => $sourceType === Video::SOURCE_HLS ? 'tenants/tenant-progress/videos/hls/1' : null,
            'duration_seconds' => $duration,
            'status' => 'ready',
        ]);
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        return [$student, $lesson, $video, $enrollment];
    }
}
