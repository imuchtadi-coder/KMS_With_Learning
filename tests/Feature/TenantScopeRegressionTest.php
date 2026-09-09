<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\GradebookItem;
use App\Models\LearningPath;
use App\Models\Question;
use App\Models\Section;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_student_cannot_open_or_enroll_in_another_tenants_learning_path(): void
    {
        [$student] = $this->tenantContext('tenant-a', 'student');
        $this->tenantContext('tenant-b', 'instructor');
        $path = LearningPath::create([
            'tenant_id' => 'tenant-b',
            'title' => 'Path tenant B',
            'published_at' => now(),
        ]);

        $this->actingAs($student)->getJson(route('student.learning-paths.show', $path))->assertNotFound();
        $this->actingAs($student)->postJson(route('student.learning-paths.enroll', $path))->assertNotFound();
    }

    public function test_tenant_admin_cannot_bulk_enroll_cross_tenant_resources(): void
    {
        [$admin] = $this->tenantContext('tenant-a', 'tenant_admin');
        [, $foreignCourse] = $this->tenantContext('tenant-b', 'instructor');
        $foreignStudent = User::factory()->create(['tenant_id' => 'tenant-b']);
        $foreignStudent->assignRole('student');

        $this->actingAs($admin)->post(route('admin.bulk.enroll'), [
            'course_id' => $foreignCourse->id,
            'user_ids' => [$foreignStudent->id],
        ])->assertSessionHasErrors(['course_id', 'user_ids.0']);

        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_tenant_admin_cannot_move_user_to_another_tenant(): void
    {
        [$admin] = $this->tenantContext('tenant-a', 'tenant_admin');
        $target = User::factory()->create(['tenant_id' => 'tenant-a']);
        $target->assignRole('student');
        Tenant::create(['id' => 'tenant-b']);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'student',
            'tenant_id' => 'tenant-b',
        ])->assertSessionHasErrors('tenant_id');

        $this->assertSame('tenant-a', $target->fresh()->tenant_id);
    }

    public function test_learning_path_cannot_receive_course_from_another_tenant(): void
    {
        [$admin] = $this->tenantContext('tenant-a', 'tenant_admin');
        [, $foreignCourse] = $this->tenantContext('tenant-b', 'instructor');
        $path = LearningPath::create(['tenant_id' => 'tenant-a', 'title' => 'Path tenant A']);

        $this->actingAs($admin)->post(route('admin.learning-paths.add-course', $path), [
            'course_id' => $foreignCourse->id,
        ])->assertSessionHasErrors('course_id');

        $this->assertDatabaseCount('learning_path_courses', 0);
    }

    public function test_gradebook_rejects_foreign_student_and_item(): void
    {
        [$instructor, $course] = $this->tenantContext('tenant-a', 'instructor');
        [$foreignStudent, $foreignCourse] = $this->tenantContext('tenant-b', 'student');
        $foreignItem = GradebookItem::create([
            'course_id' => $foreignCourse->id,
            'title' => 'Foreign item',
            'type' => 'assignment',
            'item_id' => 1,
        ]);

        $this->actingAs($instructor)->put(route('instructor.gradebook.update-grade', $course), [
            'user_id' => $foreignStudent->id,
            'gradebook_item_id' => $foreignItem->id,
            'score' => 90,
        ])->assertSessionHasErrors(['user_id', 'gradebook_item_id']);

        $this->assertDatabaseCount('grades', 0);
    }

    public function test_assignment_and_quiz_reject_relations_from_another_course(): void
    {
        [$instructor, $course, $section] = $this->tenantContext('tenant-a', 'instructor');
        [$foreignInstructor, $foreignCourse, $foreignSection] = $this->tenantContext('tenant-b', 'instructor');
        $foreignQuestion = Question::create([
            'tenant_id' => 'tenant-b',
            'course_id' => $foreignCourse->id,
            'created_by' => $foreignInstructor->id,
            'question_type' => 'essay',
            'question_text' => 'Foreign question',
            'points' => 10,
        ]);

        $this->actingAs($instructor)->post(route('instructor.assignments.store', $course), [
            'title' => 'Assignment',
            'section_id' => $foreignSection->id,
            'passing_grade' => 70,
            'max_score' => 100,
        ])->assertSessionHasErrors('section_id');

        $this->actingAs($instructor)->post(route('instructor.quizzes.store', $course), [
            'title' => 'Quiz',
            'section_id' => $section->id,
            'passing_grade' => 70,
            'max_attempts' => 1,
            'question_ids' => [$foreignQuestion->id],
        ])->assertSessionHasErrors('question_ids.0');

        $this->assertDatabaseCount('assignments', 0);
        $this->assertDatabaseCount('quizzes', 0);
    }

    private function tenantContext(string $tenantId, string $role): array
    {
        Tenant::firstOrCreate(['id' => $tenantId]);
        $user = User::factory()->create(['tenant_id' => $tenantId]);
        $user->assignRole($role);
        $course = Course::create([
            'tenant_id' => $tenantId,
            'created_by' => $user->id,
            'title' => 'Course '.$tenantId.' '.$user->id,
            'slug' => 'course-'.$tenantId.'-'.$user->id,
            'published' => true,
        ]);
        $section = Section::create(['course_id' => $course->id, 'title' => 'Section']);

        return [$user, $course, $section];
    }
}
