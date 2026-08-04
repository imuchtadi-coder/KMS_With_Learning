<?php

namespace Tests\Feature;

use App\Jobs\ProcessVideoToHLS;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoSourceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_instructor_can_add_youtube_video_without_dispatching_hls_job(): void
    {
        Bus::fake();
        [$instructor, , $lesson] = $this->courseContext('tenant-a');
        $instructor->assignRole('instructor');

        $response = $this->actingAs($instructor)->post(route('instructor.videos.store', $lesson), [
            'source_type' => Video::SOURCE_YOUTUBE,
            'title' => 'Materi dari YouTube',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ?t=10',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('videos', [
            'lesson_id' => $lesson->id,
            'source_type' => Video::SOURCE_YOUTUBE,
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'status' => 'ready',
            'original_path' => null,
        ]);
        Bus::assertNotDispatched(ProcessVideoToHLS::class);
    }

    public function test_existing_upload_without_source_type_remains_hls_and_dispatches_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        [$instructor, , $lesson] = $this->courseContext('tenant-a');
        $instructor->assignRole('instructor');

        $response = $this->actingAs($instructor)->post(route('instructor.videos.store', $lesson), [
            'title' => 'Video HLS lama',
            'video' => UploadedFile::fake()->create('materi.mp4', 100, 'video/mp4'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $video = Video::query()->sole();
        $this->assertSame(Video::SOURCE_HLS, $video->source_type);
        $this->assertSame('pending', $video->status);
        Storage::disk('local')->assertExists($video->original_path);
        Bus::assertDispatched(ProcessVideoToHLS::class, fn (ProcessVideoToHLS $job) => $job->video->is($video));
    }

    public function test_invalid_youtube_url_is_rejected(): void
    {
        Bus::fake();
        [$instructor, , $lesson] = $this->courseContext('tenant-a');
        $instructor->assignRole('instructor');

        $response = $this->actingAs($instructor)
            ->from('/instructor/courses')
            ->post(route('instructor.videos.store', $lesson), [
                'source_type' => Video::SOURCE_YOUTUBE,
                'title' => 'URL palsu',
                'youtube_url' => 'https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ',
            ]);

        $response->assertRedirect('/instructor/courses');
        $response->assertSessionHasErrors('youtube_url');
        $this->assertDatabaseCount('videos', 0);
        Bus::assertNotDispatched(ProcessVideoToHLS::class);
    }

    public function test_instructor_cannot_add_video_to_course_from_another_tenant(): void
    {
        [, , $lesson] = $this->courseContext('tenant-a');
        Tenant::create(['id' => 'tenant-b']);
        $outsider = User::factory()->create(['tenant_id' => 'tenant-b']);
        $outsider->assignRole('instructor');

        $this->actingAs($outsider)->postJson(route('instructor.videos.store', $lesson), [
            'source_type' => Video::SOURCE_YOUTUBE,
            'title' => 'Tidak diizinkan',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertForbidden();

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_enrolled_student_receives_privacy_enhanced_youtube_playback_url(): void
    {
        [, $course, $lesson] = $this->courseContext('tenant-a', true);
        $student = User::factory()->create(['tenant_id' => 'tenant-a']);
        $student->assignRole('student');
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
        $video = Video::create([
            'lesson_id' => $lesson->id,
            'tenant_id' => 'tenant-a',
            'title' => 'YouTube siap putar',
            'source_type' => Video::SOURCE_YOUTUBE,
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'ready',
        ]);

        $this->actingAs($student, 'sanctum');

        $this->getJson('/api/videos/'.$video->id.'/play')
            ->assertOk()
            ->assertJson([
                'id' => $video->id,
                'source_type' => Video::SOURCE_YOUTUBE,
                'url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            ]);
    }

    public function test_youtube_video_can_be_deleted_without_touching_storage(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$instructor, , $lesson] = $this->courseContext('tenant-a');
        $instructor->assignRole('instructor');
        $video = Video::create([
            'lesson_id' => $lesson->id,
            'tenant_id' => 'tenant-a',
            'title' => 'YouTube untuk dihapus',
            'source_type' => Video::SOURCE_YOUTUBE,
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'status' => 'ready',
        ]);

        $this->actingAs($instructor)
            ->delete(route('instructor.videos.destroy', $video))
            ->assertRedirect();

        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_tenant_admin_can_add_youtube_video_through_api_for_same_tenant(): void
    {
        Bus::fake();
        [, , $lesson] = $this->courseContext('tenant-a');
        $tenantAdmin = User::factory()->create(['tenant_id' => 'tenant-a']);
        $tenantAdmin->assignRole('tenant_admin');

        $this->actingAs($tenantAdmin, 'sanctum')->postJson('/api/videos/upload', [
            'lesson_id' => $lesson->id,
            'source_type' => Video::SOURCE_YOUTUBE,
            'title' => 'YouTube oleh tenant admin',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertCreated()->assertJsonPath('video.source_type', Video::SOURCE_YOUTUBE);

        Bus::assertNotDispatched(ProcessVideoToHLS::class);
    }

    public function test_student_cannot_add_video_through_api(): void
    {
        [, , $lesson] = $this->courseContext('tenant-a');
        $student = User::factory()->create(['tenant_id' => 'tenant-a']);
        $student->assignRole('student');

        $this->actingAs($student, 'sanctum')->postJson('/api/videos/upload', [
            'lesson_id' => $lesson->id,
            'source_type' => Video::SOURCE_YOUTUBE,
            'title' => 'Tidak boleh ditambahkan siswa',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertForbidden();

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_superadmin_cannot_bypass_tenant_boundary_when_adding_video(): void
    {
        [, , $lesson] = $this->courseContext('tenant-a');
        $superadmin = User::factory()->create(['tenant_id' => null]);
        $superadmin->assignRole('superadmin');

        $this->actingAs($superadmin, 'sanctum')->postJson('/api/videos/upload', [
            'lesson_id' => $lesson->id,
            'source_type' => Video::SOURCE_YOUTUBE,
            'title' => 'Tidak boleh melintasi tenant',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ])->assertForbidden();

        $this->assertDatabaseCount('videos', 0);
    }

    private function courseContext(string $tenantId, bool $published = false): array
    {
        Tenant::create(['id' => $tenantId]);
        $instructor = User::factory()->create(['tenant_id' => $tenantId]);
        $course = Course::create([
            'tenant_id' => $tenantId,
            'created_by' => $instructor->id,
            'title' => 'Kursus '.$tenantId,
            'slug' => 'kursus-'.$tenantId,
            'published' => $published,
        ]);
        $section = Section::create([
            'course_id' => $course->id,
            'title' => 'Bagian 1',
        ]);
        $lesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Pelajaran 1',
        ]);

        return [$instructor, $course, $lesson];
    }
}
