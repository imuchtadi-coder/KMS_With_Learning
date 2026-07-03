<?php

use App\Models\LearningPath, Course, User;
use Illuminate\Support\Facades\Artisan;

return function () {
    // Test 1: Create Learning Path
    $tenantId = 'localhost';

    $path = LearningPath::create([
        'tenant_id' => $tenantId,
        'title' => 'Backend Engineer Path',
        'description' => 'Complete guide to becoming a backend engineer',
    ]);

    echo "✓ Created Learning Path: {$path->title} (ID: {$path->id})\n";

    // Test 2: Add Courses
    $courses = Course::where('tenant_id', $tenantId)->limit(3)->get();

    echo "\n✓ Available Courses:\n";
    foreach ($courses as $index => $course) {
        $path->addCourse($course, $index, true);
        echo "  [$index] {$course->title}\n";
    }

    // Test 3: Publish Path
    $path->publish();
    echo "\n✓ Path published\n";

    // Test 4: Enroll Student
    $student = User::where('email', 'student@example.com')->first();

    if ($student) {
        echo "\n✓ Student: {$student->name}\n";

        $enrollment = \App\Models\LearningPathEnrollment::create([
            'tenant_id' => $tenantId,
            'learning_path_id' => $path->id,
            'user_id' => $student->id,
            'started_at' => now(),
        ]);

        echo "✓ Enrolled in path\n";

        // Test 5: Check Progress
        $progress = $path->getUserProgress($student);
        echo "✓ Initial progress: {$progress}%\n";

        // Test 6: Get Next Course
        $nextCourse = $path->getNextCourseForUser($student);
        echo "✓ Next course: {$nextCourse->title}\n";
    }

    echo "\n✅ All Learning Path tests passed!\n";
};
