<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\User;
use App\Policies\CoursePolicy;
use Mockery;
use PHPUnit\Framework\TestCase;

class CoursePolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_published_course_cannot_be_viewed_across_tenants(): void
    {
        $user = $this->userWithRoles(['student']);
        $user->tenant_id = 'tenant-a';

        $course = new Course([
            'tenant_id' => 'tenant-b',
            'published' => true,
        ]);

        $this->assertFalse((new CoursePolicy)->view($user, $course));
    }

    public function test_tenant_admin_cannot_view_draft_from_another_tenant(): void
    {
        $user = $this->userWithRoles(['tenant_admin']);
        $user->tenant_id = 'tenant-a';

        $course = new Course([
            'tenant_id' => 'tenant-b',
            'created_by' => 99,
            'published' => false,
        ]);

        $this->assertFalse((new CoursePolicy)->view($user, $course));
    }

    public function test_course_owner_can_view_draft_in_same_tenant(): void
    {
        $user = $this->userWithRoles(['instructor']);
        $user->id = 10;
        $user->tenant_id = 'tenant-a';

        $course = new Course([
            'tenant_id' => 'tenant-a',
            'created_by' => 10,
            'published' => false,
        ]);

        $this->assertTrue((new CoursePolicy)->view($user, $course));
    }

    private function userWithRoles(array $roles): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')
            ->andReturnUsing(fn ($role) => is_array($role)
                ? count(array_intersect($role, $roles)) > 0
                : in_array($role, $roles, true));

        return $user;
    }
}
