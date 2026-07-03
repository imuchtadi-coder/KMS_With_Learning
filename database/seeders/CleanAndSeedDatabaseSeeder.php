<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Material;
use App\Models\Video;
use App\Models\Progress;
use App\Models\Certificate;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizQuestion;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\GradebookItem;
use App\Models\Grade;
use App\Models\LearningPath;
use App\Models\LiveClass;
use App\Models\Badge;
use App\Models\UserGamification;
use App\Models\PointTransaction;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CleanAndSeedDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanData();
        $this->resetSequences();
        $this->ensureSuperadmin();
        $this->seedTenants();
    }

    private function cleanData(): void
    {
        $tables = [
            'quiz_answers',
            'quiz_attempts',
            'quiz_questions',
            'quizzes',
            'questions',
            'assignment_submissions',
            'assignments',
            'grades',
            'gradebook_items',
            'certificates',
            'learning_path_progress',
            'learning_path_enrollments',
            'learning_path_courses',
            'learning_paths',
            'progress',
            'enrollments',
            'materials',
            'videos',
            'lessons',
            'sections',
            'courses',
            'model_has_roles',
            'model_has_permissions',
            'users',
            'domains',
            'tenants',
            'course_reviews',
            'discussions',
            'discussion_replies',
            'badges',
            'badge_user',
            'point_transactions',
            'user_gamification',
            'live_classes',
        ];

        foreach ($tables as $table) {
            DB::statement("TRUNCATE TABLE \"{$table}\" CASCADE;");
        }
    }

    private function resetSequences(): void
    {
        $sequences = [
            'users_id_seq',
            'courses_id_seq',
            'sections_id_seq',
            'lessons_id_seq',
            'videos_id_seq',
            'materials_id_seq',
            'enrollments_id_seq',
            'progress_id_seq',
            'certificates_id_seq',
            'learning_paths_id_seq',
            'learning_path_courses_id_seq',
            'learning_path_enrollments_id_seq',
            'learning_path_progress_id_seq',
            'questions_id_seq',
            'quizzes_id_seq',
            'quiz_questions_id_seq',
            'quiz_attempts_id_seq',
            'quiz_answers_id_seq',
            'assignments_id_seq',
            'assignment_submissions_id_seq',
            'gradebook_items_id_seq',
            'grades_id_seq',
            'discussions_id_seq',
            'discussion_replies_id_seq',
            'live_classes_id_seq',
            'badges_id_seq',
            'point_transactions_id_seq',
            'user_gamification_id_seq',
        ];

        foreach ($sequences as $seq) {
            DB::statement("ALTER SEQUENCE \"{$seq}\" RESTART WITH 1;");
        }
    }

    private function seedTenants(): void
    {
        $tenants = [
            [
                'id' => 'tenant-a',
                'name' => 'Tenant A - Universitas Teknologi',
                'domain' => 'tenant-a.kitsuluh.test',
                'courses' => [
                    [
                        'title' => 'Web Development Fundamentals',
                        'slug' => 'web-development-fundamentals',
                        'description' => 'Learn HTML, CSS, and JavaScript from scratch.',
                        'level' => 'beginner',
                        'price' => 0,
                        'published' => true,
                        'sections' => [
                            [
                                'title' => 'Introduction to HTML',
                                'lessons' => [
                                    ['title' => 'HTML Basics', 'description' => 'Learn the structure of HTML documents'],
                                    ['title' => 'HTML Forms', 'description' => 'Create forms with input fields'],
                                ],
                                'quiz' => ['title' => 'HTML Fundamentals Quiz', 'passing_grade' => 70, 'time_limit' => 15, 'max_attempts' => 3],
                            ],
                            [
                                'title' => 'CSS Styling',
                                'lessons' => [
                                    ['title' => 'CSS Selectors', 'description' => 'Understanding selectors and specificity'],
                                    ['title' => 'Flexbox Layout', 'description' => 'Master flexbox for responsive layouts'],
                                ],
                                'assignment' => ['title' => 'Build a Landing Page', 'description' => 'Build a responsive landing page', 'deadline_days' => 7],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Advanced JavaScript',
                        'slug' => 'advanced-javascript',
                        'description' => 'Master closures, promises, and async patterns.',
                        'level' => 'advanced',
                        'price' => 149.99,
                        'published' => false,
                        'sections' => [
                            [
                                'title' => 'Core Concepts',
                                'lessons' => [
                                    ['title' => 'Closures in Depth', 'description' => 'Understanding lexical scoping'],
                                    ['title' => 'The Event Loop', 'description' => 'How JavaScript handles async operations'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'tenant-b',
                'name' => 'Tenant B - PT Inovasi Digital',
                'domain' => 'tenant-b.kitsuluh.test',
                'courses' => [
                    [
                        'title' => 'Python for Data Science',
                        'slug' => 'python-for-data-science',
                        'description' => 'Learn Python with pandas, numpy, and matplotlib.',
                        'level' => 'intermediate',
                        'price' => 199.99,
                        'published' => true,
                        'sections' => [
                            [
                                'title' => 'Python Essentials',
                                'lessons' => [
                                    ['title' => 'Data Types & Variables', 'description' => 'Python basic data types'],
                                    ['title' => 'Control Flow', 'description' => 'Loops and conditionals'],
                                ],
                                'quiz' => ['title' => 'Python Basics Quiz', 'passing_grade' => 70, 'time_limit' => 10, 'max_attempts' => 2],
                            ],
                            [
                                'title' => 'Data Analysis with Pandas',
                                'lessons' => [
                                    ['title' => 'DataFrames', 'description' => 'Working with tabular data'],
                                    ['title' => 'Data Cleaning', 'description' => 'Handling missing data'],
                                ],
                                'assignment' => ['title' => 'Data Analysis Project', 'description' => 'Analyze a real dataset', 'deadline_days' => 14],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Digital Marketing 101',
                        'slug' => 'digital-marketing-101',
                        'description' => 'SEO, social media, and content marketing basics.',
                        'level' => 'beginner',
                        'price' => 0,
                        'published' => true,
                        'sections' => [
                            [
                                'title' => 'SEO Fundamentals',
                                'lessons' => [
                                    ['title' => 'Keyword Research', 'description' => 'Finding the right keywords'],
                                    ['title' => 'On-Page SEO', 'description' => 'Optimizing page content'],
                                ],
                                'quiz' => ['title' => 'SEO Knowledge Check', 'passing_grade' => 60, 'time_limit' => 10, 'max_attempts' => 3],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::create(['id' => $data['id']]);
            $tenant->name = $data['name'];
            $tenant->save();

            Domain::create([
                'domain' => $data['domain'],
                'tenant_id' => $tenant->id,
            ]);

            // Categories per tenant
            $categoryNames = ['Web Development', 'Data Science', 'Design', 'Business', 'Marketing'];
            $categories = [];
            foreach ($categoryNames as $name) {
                $categories[] = Category::create([
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'slug' => str($name)->slug(),
                ]);
            }

            // Tags per tenant
            $tagNames = ['beginner-friendly', 'project-based', 'certificate', 'self-paced', 'live-session',
                         'recorded', 'intermediate', 'advanced', 'hands-on', 'theory'];
            $tags = [];
            foreach ($tagNames as $name) {
                $tags[] = Tag::create([
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'slug' => str($name)->slug(),
                ]);
            }

            $admin = User::create([
                'name' => "Admin - {$data['name']}",
                'email' => "admin@{$data['id']}.kitsuluh.test",
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
            ]);
            $admin->assignRole('tenant_admin');

            $instructor = User::create([
                'name' => "Instructor - {$data['name']}",
                'email' => "instructor@{$data['id']}.kitsuluh.test",
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
            ]);
            $instructor->assignRole('instructor');

            $students = [];
            for ($i = 1; $i <= 5; $i++) {
                $student = User::create([
                    'name' => "Student {$i} - {$data['name']}",
                    'email' => "student{$i}@{$data['id']}.kitsuluh.test",
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'tenant_id' => $tenant->id,
                ]);
                $student->assignRole('student');
                $students[] = $student;
            }

            // Seed courses with assessments
            foreach ($data['courses'] as $courseData) {
                $course = Course::create([
                    'title' => $courseData['title'],
                    'slug' => $courseData['slug'],
                    'description' => $courseData['description'],
                    'level' => $courseData['level'],
                    'price' => $courseData['price'],
                    'published' => $courseData['published'],
                    'tenant_id' => $tenant->id,
                    'created_by' => $instructor->id,
                    'category_id' => $categories[array_rand($categories)]->id,
                ]);

                // Attach random tags to the course
                $course->tags()->attach(
                    collect($tags)->random(rand(2, 4))->pluck('id')->toArray()
                );

                // Enroll all students in published courses
                if ($courseData['published']) {
                    foreach ($students as $student) {
                        Enrollment::create([
                            'user_id' => $student->id,
                            'course_id' => $course->id,
                            'status' => 'active',
                            'enrolled_at' => now(),
                        ]);
                    }

                    // Add sample reviews for published courses
                    $reviewContents = ['Great course! Very well structured and easy to follow.', 'Excellent content and practical examples.', 'Good introduction but could use more advanced topics.', 'Perfect for beginners. Highly recommended!', 'Learned a lot from this course. The instructor explains concepts clearly.'];
                    foreach ($students as $i => $student) {
                        if ($i < 3) {
                            $course->reviews()->create([
                                'user_id' => $student->id,
                                'rating' => rand(3, 5),
                                'review' => $reviewContents[$i],
                            ]);
                        }
                    }
                    $course->recalculateRating();
                }

                $sectionOrder = 1;
                foreach ($courseData['sections'] as $sectionData) {
                    $section = Section::create([
                        'course_id' => $course->id,
                        'title' => $sectionData['title'],
                        'order' => $sectionOrder++,
                    ]);

                    $lessonOrder = 1;
                    foreach ($sectionData['lessons'] as $lessonData) {
                        Lesson::create([
                            'section_id' => $section->id,
                            'title' => $lessonData['title'],
                            'description' => $lessonData['description'],
                            'order' => $lessonOrder++,
                        ]);
                    }

                    // Create quiz if defined
                    if (isset($sectionData['quiz'])) {
                        $quiz = Quiz::create([
                            'course_id' => $course->id,
                            'section_id' => $section->id,
                            'title' => $sectionData['quiz']['title'],
                            'passing_grade' => $sectionData['quiz']['passing_grade'],
                            'time_limit' => $sectionData['quiz']['time_limit'],
                            'max_attempts' => $sectionData['quiz']['max_attempts'],
                            'published' => true,
                            'created_by' => $instructor->id,
                        ]);

                        // Create 3 questions per quiz
                        $questionsData = [
                            [
                                'type' => 'multiple_choice',
                                'text' => 'What does HTML stand for?',
                                'options' => json_encode(['HyperText Markup Language', 'HighText Machine Language', 'HyperTool Markup Language', 'Home Tool Markup Language']),
                                'answer' => 'HyperText Markup Language',
                            ],
                            [
                                'type' => 'multiple_choice',
                                'text' => 'Which CSS property controls the layout direction?',
                                'options' => json_encode(['display', 'flex-direction', 'position', 'float']),
                                'answer' => 'flex-direction',
                            ],
                            [
                                'type' => 'true_false',
                                'text' => 'JavaScript is a compiled language.',
                                'options' => json_encode(['True', 'False']),
                                'answer' => 'False',
                            ],
                        ];

                        foreach ($questionsData as $qIdx => $qData) {
                            $question = Question::create([
                                'tenant_id' => $tenant->id,
                                'course_id' => $course->id,
                                'created_by' => $instructor->id,
                                'question_type' => $qData['type'],
                                'question_text' => $qData['text'],
                                'options' => $qData['options'],
                                'correct_answer' => $qData['answer'],
                                'points' => 10,
                            ]);

                            QuizQuestion::create([
                                'quiz_id' => $quiz->id,
                                'question_id' => $question->id,
                                'order' => $qIdx + 1,
                                'points' => 10,
                            ]);
                        }

                        // Create gradebook item for quiz
                        $gradebookItem = GradebookItem::create([
                            'course_id' => $course->id,
                            'title' => $quiz->title,
                            'type' => 'quiz',
                            'item_id' => $quiz->id,
                            'weight' => 50,
                            'max_score' => 30,
                        ]);

                        // Seed grades for enrolled students
                        if ($courseData['published']) {
                            foreach ($students as $student) {
                                $score = rand(5, 30);
                                $percentage = round(($score / 30) * 100, 2);
                                Grade::create([
                                    'user_id' => $student->id,
                                    'course_id' => $course->id,
                                    'gradebook_item_id' => $gradebookItem->id,
                                    'score' => $score,
                                    'percentage' => $percentage,
                                    'letter_grade' => $percentage >= 90 ? 'A' : ($percentage >= 80 ? 'B' : ($percentage >= 70 ? 'C' : ($percentage >= 60 ? 'D' : 'E'))),
                                ]);
                            }
                        }
                    }

                    // Create assignment if defined
                    if (isset($sectionData['assignment'])) {
                        $assignment = Assignment::create([
                            'course_id' => $course->id,
                            'section_id' => $section->id,
                            'title' => $sectionData['assignment']['title'],
                            'description' => $sectionData['assignment']['description'],
                            'deadline' => now()->addDays($sectionData['assignment']['deadline_days']),
                            'max_score' => 100,
                            'created_by' => $instructor->id,
                        ]);

                        // Create gradebook item for assignment
                        $gradebookItem = GradebookItem::create([
                            'course_id' => $course->id,
                            'title' => $assignment->title,
                            'type' => 'assignment',
                            'item_id' => $assignment->id,
                            'weight' => 50,
                            'max_score' => 100,
                        ]);

                        // Seed grades for enrolled students
                        if ($courseData['published']) {
                            foreach ($students as $student) {
                                $score = rand(40, 100);
                                $percentage = $score;
                                Grade::create([
                                    'user_id' => $student->id,
                                    'course_id' => $course->id,
                                    'gradebook_item_id' => $gradebookItem->id,
                                    'score' => $score,
                                    'percentage' => $percentage,
                                    'letter_grade' => $percentage >= 90 ? 'A' : ($percentage >= 80 ? 'B' : ($percentage >= 70 ? 'C' : ($percentage >= 60 ? 'D' : 'E'))),
                                ]);
                            }
                        }
                    }
                } // end section loop

                // ==========================================
                // DEMO DATA (per course)
                // ==========================================

                $allLessons = Lesson::whereIn('section_id', Section::where('course_id', $course->id)->pluck('id'))
                    ->orderBy('section_id')->orderBy('order')->get();

                // --- Materials (1 PDF + 1 PPTX per lesson) ---
                foreach ($allLessons as $lesson) {
                    $slug = str($lesson->title)->slug();
                    $tenantPrefix = 'tenants/' . $tenant->id . '/materials/demo';
                    Material::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'title' => $lesson->title . ' - Notes'],
                        ['tenant_id' => $tenant->id, 'file_path' => $tenantPrefix . '/' . $slug . '.pdf', 'file_type' => 'pdf', 'file_size' => 0]
                    );
                    Material::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'title' => $lesson->title . ' - Slide'],
                        ['tenant_id' => $tenant->id, 'file_path' => $tenantPrefix . '/' . $slug . '.pptx', 'file_type' => 'pptx', 'file_size' => 0]
                    );
                }

                // --- Videos (1 per lesson, placeholder) ---
                foreach ($allLessons as $lesson) {
                    Video::updateOrCreate(
                        ['lesson_id' => $lesson->id],
                        ['tenant_id' => $tenant->id, 'title' => $lesson->title . ' - Video', 'original_path' => null, 'hls_path' => null, 'status' => 'ready', 'duration_seconds' => 0]
                    );
                }

                if ($courseData['published']) {
                    $lessonIds = $allLessons->pluck('id');

                    // --- Progress (~50% completed per student) ---
                    foreach ($students as $student) {
                        $halfCount = max(1, (int) ceil($lessonIds->count() / 2));
                        $completedLessons = $lessonIds->random(min($halfCount, $lessonIds->count()));
                        foreach ($completedLessons as $lessonId) {
                            Progress::updateOrCreate(
                                ['user_id' => $student->id, 'lesson_id' => $lessonId],
                                ['completed' => true]
                            );
                        }
                    }

                    // --- Certificates (for fully completed courses) ---
                    foreach ($students as $student) {
                        $completedCount = Progress::where('user_id', $student->id)
                            ->whereIn('lesson_id', $lessonIds)->where('completed', true)->count();
                        if ($completedCount === $lessonIds->count()) {
                            Certificate::firstOrCreate(
                                ['user_id' => $student->id, 'course_id' => $course->id],
                                ['certificate_number' => 'CERT-DEMO-' . $student->id . '-' . $course->id, 'issued_at' => now()]
                            );
                        }
                    }

                    // --- Discussions (2 per course) ---
                    $discussionTitles = [
                        'Welcome to ' . $course->title . ' — Introduce Yourself!',
                        'Q&A: ' . ($allLessons->first()?->title ?? 'Lesson 1') . ' — Ask your questions here',
                    ];
                    $discussionContents = [
                        'Welcome everyone! Please introduce yourself and share what you hope to learn from this course.',
                        'If you have any questions about the material in this lesson, feel free to ask here.',
                    ];
                    $savedDiscussions = [];
                    foreach ($discussionTitles as $idx => $title) {
                        $disc = Discussion::create([
                            'course_id' => $course->id,
                            'lesson_id' => $idx === 1 ? $allLessons->first()->id : null,
                            'title' => $title,
                            'content' => $discussionContents[$idx],
                            'created_by' => $instructor->id,
                            'is_pinned' => $idx === 0,
                        ]);
                        $savedDiscussions[] = $disc;
                    }

                    // --- Discussion Replies ---
                    $replyTemplates = [
                        ['content' => 'Hi everyone! I am excited to start this course.', 'studentIdx' => 0],
                        ['content' => 'Great to be here! The course looks very comprehensive.', 'studentIdx' => 1],
                        ['content' => 'I found the examples very helpful for understanding this topic.', 'studentIdx' => 2],
                        ['content' => 'Thanks for the explanation! This makes much more sense now.', 'studentIdx' => 3],
                    ];
                    foreach ($savedDiscussions as $disc) {
                        foreach ($replyTemplates as $reply) {
                            DiscussionReply::create([
                                'discussion_id' => $disc->id,
                                'content' => $reply['content'],
                                'created_by' => $students[$reply['studentIdx']]->id,
                            ]);
                        }
                    }

                    // --- Live Classes (1 past + 1 upcoming) ---
                    LiveClass::create([
                        'tenant_id' => $tenant->id, 'course_id' => $course->id,
                        'title' => 'Kickoff Session: ' . $course->title,
                        'description' => 'Introduction and overview of the course',
                        'provider' => 'jitsi', 'start_time' => now()->subDays(rand(3, 7)),
                        'end_time' => now()->subDays(rand(2, 6))->addHours(1),
                        'status' => 'ended', 'created_by' => $instructor->id,
                    ]);
                    LiveClass::create([
                        'tenant_id' => $tenant->id, 'course_id' => $course->id,
                        'title' => 'Live Q&A — ' . $course->title,
                        'description' => 'Live question and answer session',
                        'provider' => 'google_meet', 'start_time' => now()->addDays(rand(5, 14)),
                        'end_time' => now()->addDays(rand(5, 14))->addHours(1),
                        'status' => 'scheduled', 'created_by' => $instructor->id,
                    ]);
                }
            } // end course loop

            // ==========================================
            // Learning Paths (1 per tenant)
            // ==========================================
            $publishedCourses = Course::where('tenant_id', $tenant->id)->where('published', true)->get();
            if ($publishedCourses->isNotEmpty()) {
                $lpNames = [
                    'tenant-a' => ['title' => 'Web Developer Track', 'slug' => 'web-developer-track'],
                    'tenant-b' => ['title' => 'Data & Marketing Track', 'slug' => 'data-marketing-track'],
                ];
                $lpInfo = $lpNames[$data['id']] ?? ['title' => 'Learning Path', 'slug' => 'learning-path'];
                $lp = LearningPath::create([
                    'tenant_id' => $tenant->id,
                    'title' => $lpInfo['title'],
                    'slug' => $lpInfo['slug'],
                    'description' => 'Complete this path to master the essential skills.',
                    'published_at' => now(),
                ]);
                foreach ($publishedCourses as $order => $pc) {
                    DB::table('learning_path_courses')->insert([
                        'tenant_id' => $tenant->id,
                        'learning_path_id' => $lp->id,
                        'course_id' => $pc->id,
                        'order' => $order + 1,
                        'is_mandatory' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                foreach ($students as $student) {
                    DB::table('learning_path_enrollments')->insert([
                        'tenant_id' => $tenant->id,
                        'learning_path_id' => $lp->id,
                        'user_id' => $student->id,
                        'started_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ==========================================
            // Gamification Demo Data
            // ==========================================
            $badgeDefinitions = [
                ['name' => 'Quick Starter', 'slug' => 'quick-starter', 'description' => 'Complete your first lesson', 'image_url' => null, 'active' => true, 'criteria_type' => 'lessons_completed', 'criteria_value' => 1],
                ['name' => 'Dedicated Learner', 'slug' => 'dedicated-learner', 'description' => 'Complete 10 lessons', 'image_url' => null, 'active' => true, 'criteria_type' => 'lessons_completed', 'criteria_value' => 10],
                ['name' => 'Quiz Master', 'slug' => 'quiz-master', 'description' => 'Score 100% on any quiz', 'image_url' => null, 'active' => true, 'criteria_type' => 'quiz_perfect', 'criteria_value' => 1],
            ];
            $createdBadges = [];
            foreach ($badgeDefinitions as $bd) {
                $slug = $bd['slug'] . '-' . $tenant->id;
                $badge = Badge::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'slug' => $slug],
                    array_merge($bd, ['slug' => $slug])
                );
                $createdBadges[] = $badge;
            }

            foreach ($students as $student) {
                $totalCompleted = Progress::where('user_id', $student->id)->where('completed', true)->count();
                $xpFromLessons = $totalCompleted * 10;
                $xpFromQuizzes = rand(0, 2) * 30;
                $totalXp = $xpFromLessons + $xpFromQuizzes;

                UserGamification::updateOrCreate(
                    ['user_id' => $student->id],
                    [
                        'total_xp' => $totalXp,
                        'current_streak' => rand(1, 5),
                        'longest_streak' => rand(3, 10),
                        'level' => max(1, (int) floor(sqrt($totalXp / 100)) + 1),
                    ]
                );

                $completedProgress = Progress::where('user_id', $student->id)->where('completed', true)
                    ->with('lesson.section')->get();
                $seenCourses = [];
                foreach ($completedProgress as $p) {
                    $cid = $p->lesson->section->course_id ?? null;
                    if ($cid && !isset($seenCourses[$cid])) {
                        $seenCourses[$cid] = true;
                        PointTransaction::create([
                            'tenant_id' => $tenant->id,
                            'user_id' => $student->id,
                            'course_id' => $cid,
                            'points' => 10,
                            'action' => 'lesson_completed',
                        ]);
                    }
                }

                $badgesToAward = collect($createdBadges)->random(rand(1, 2));
                foreach ($badgesToAward as $badge) {
                    DB::table('badge_user')->updateOrInsert(
                        ['badge_id' => $badge->id, 'user_id' => $student->id],
                        ['earned_at' => now()]
                    );
                }
            }
        } // end tenant loop
    }

    private function ensureSuperadmin(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@krakatau-it.co.id',
            'password' => Hash::make('@lM4brur1'),
            'email_verified_at' => now(),
            'tenant_id' => null,
        ]);
        $superadmin->assignRole('superadmin');
    }
}
