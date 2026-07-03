<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Policies\CoursePolicy;
use App\Policies\SectionPolicy;
use App\Policies\LessonPolicy;
use App\Policies\QuizPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\DiscussionPolicy;
use App\Policies\DiscussionReplyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        Section::class => SectionPolicy::class,
        Lesson::class => LessonPolicy::class,
        Quiz::class => QuizPolicy::class,
        Assignment::class => AssignmentPolicy::class,
        Discussion::class => DiscussionPolicy::class,
        DiscussionReply::class => DiscussionReplyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
