<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Badge;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradebookItem;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\Material;
use App\Models\PointTransaction;
use App\Models\Progress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Section;
use App\Models\User;
use App\Models\UserGamification;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            'tenant-a' => ['instructor_id' => 3],
            'tenant-b' => ['instructor_id' => 10],
        ];

        $extraCourses = [
            'tenant-a' => [
                [
                    'title' => 'UI/UX Design Principles',
                    'slug' => 'ui-ux-design-principles',
                    'description' => 'Learn user research, wireframing, prototyping, and usability testing.',
                    'level' => 'intermediate',
                    'price' => 0,
                    'published' => true,
                    'sections' => [
                        [
                            'title' => 'Design Thinking',
                            'lessons' => [
                                ['title' => 'Empathy & User Research', 'description' => 'Understanding user needs through research'],
                                ['title' => 'Ideation & Prototyping', 'description' => 'From ideas to interactive prototypes'],
                            ],
                            'quiz' => ['title' => 'Design Thinking Quiz', 'passing_grade' => 70, 'time_limit' => 15, 'max_attempts' => 3],
                        ],
                        [
                            'title' => 'Visual Design',
                            'lessons' => [
                                ['title' => 'Color Theory & Typography', 'description' => 'Master color palettes and font pairing'],
                                ['title' => 'Usability Heuristics', 'description' => '10 usability heuristics for UI evaluation'],
                            ],
                            'assignment' => ['title' => 'Design a Mobile App UI', 'description' => 'Create a complete mobile app UI design', 'deadline_days' => 10],
                        ],
                    ],
                ],
                [
                    'title' => 'React.js for Modern Web',
                    'slug' => 'reactjs-for-modern-web',
                    'description' => 'Build dynamic single-page applications with React, hooks, and state management.',
                    'level' => 'advanced',
                    'price' => 199.99,
                    'published' => true,
                    'sections' => [
                        [
                            'title' => 'React Fundamentals',
                            'lessons' => [
                                ['title' => 'Components & Props', 'description' => 'Building reusable UI components'],
                                ['title' => 'State & Lifecycle', 'description' => 'Managing component state with hooks'],
                            ],
                            'quiz' => ['title' => 'React Basics Quiz', 'passing_grade' => 70, 'time_limit' => 15, 'max_attempts' => 3],
                        ],
                        [
                            'title' => 'Advanced Patterns',
                            'lessons' => [
                                ['title' => 'Context API & Redux', 'description' => 'Global state management solutions'],
                                ['title' => 'Performance Optimization', 'description' => 'Memoization, lazy loading, and code splitting'],
                            ],
                            'assignment' => ['title' => 'Build a Dashboard App', 'description' => 'Create a full-featured admin dashboard', 'deadline_days' => 14],
                        ],
                    ],
                ],
                [
                    'title' => 'Database Design & SQL',
                    'slug' => 'database-design-sql',
                    'description' => 'Understand relational databases, normalization, and complex queries.',
                    'level' => 'intermediate',
                    'price' => 149.99,
                    'published' => false,
                    'sections' => [
                        [
                            'title' => 'Relational Database Concepts',
                            'lessons' => [
                                ['title' => 'Normalization & ERD', 'description' => 'Designing efficient database schemas'],
                                ['title' => 'CRUD Operations', 'description' => 'INSERT, SELECT, UPDATE, DELETE queries'],
                            ],
                            'quiz' => ['title' => 'SQL Fundamentals Quiz', 'passing_grade' => 65, 'time_limit' => 20, 'max_attempts' => 3],
                        ],
                    ],
                ],
            ],
            'tenant-b' => [
                [
                    'title' => 'Machine Learning Fundamentals',
                    'slug' => 'machine-learning-fundamentals',
                    'description' => 'Explore supervised and unsupervised learning with scikit-learn.',
                    'level' => 'advanced',
                    'price' => 249.99,
                    'published' => true,
                    'sections' => [
                        [
                            'title' => 'Supervised Learning',
                            'lessons' => [
                                ['title' => 'Linear & Logistic Regression', 'description' => 'Predicting continuous and categorical values'],
                                ['title' => 'Decision Trees & Random Forests', 'description' => 'Ensemble methods for better accuracy'],
                            ],
                            'quiz' => ['title' => 'ML Concepts Quiz', 'passing_grade' => 70, 'time_limit' => 20, 'max_attempts' => 2],
                        ],
                        [
                            'title' => 'Unsupervised Learning',
                            'lessons' => [
                                ['title' => 'K-Means Clustering', 'description' => 'Grouping unlabeled data'],
                                ['title' => 'Principal Component Analysis', 'description' => 'Dimensionality reduction techniques'],
                            ],
                            'assignment' => ['title' => 'ML Model Comparison', 'description' => 'Compare multiple models on a real dataset', 'deadline_days' => 14],
                        ],
                    ],
                ],
                [
                    'title' => 'DevOps & Cloud Computing',
                    'slug' => 'devops-cloud-computing',
                    'description' => 'Learn CI/CD, Docker, Kubernetes, and cloud deployment strategies.',
                    'level' => 'intermediate',
                    'price' => 179.99,
                    'published' => true,
                    'sections' => [
                        [
                            'title' => 'Containerization',
                            'lessons' => [
                                ['title' => 'Docker Basics', 'description' => 'Building and running containers'],
                                ['title' => 'Docker Compose', 'description' => 'Orchestrating multi-container applications'],
                            ],
                            'quiz' => ['title' => 'Docker Quiz', 'passing_grade' => 70, 'time_limit' => 15, 'max_attempts' => 3],
                        ],
                        [
                            'title' => 'CI/CD & Deployment',
                            'lessons' => [
                                ['title' => 'GitHub Actions', 'description' => 'Automating build and deploy pipelines'],
                                ['title' => 'Cloud Deployment (AWS)', 'description' => 'Deploying applications to AWS EC2 and ECS'],
                            ],
                            'assignment' => ['title' => 'CI/CD Pipeline Setup', 'description' => 'Create a complete CI/CD pipeline', 'deadline_days' => 10],
                        ],
                    ],
                ],
                [
                    'title' => 'Mobile App Development with Flutter',
                    'slug' => 'mobile-app-development-flutter',
                    'description' => 'Build cross-platform mobile apps using Dart and Flutter framework.',
                    'level' => 'intermediate',
                    'price' => 199.99,
                    'published' => false,
                    'sections' => [
                        [
                            'title' => 'Dart & Flutter Basics',
                            'lessons' => [
                                ['title' => 'Dart Language Fundamentals', 'description' => 'Variables, functions, and classes in Dart'],
                                ['title' => 'Widgets & Layouts', 'description' => 'Building UI with Flutter widgets'],
                            ],
                            'quiz' => ['title' => 'Flutter Basics Quiz', 'passing_grade' => 65, 'time_limit' => 15, 'max_attempts' => 3],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tenants as $tenantId => $config) {
            $instructor = User::findOrFail($config['instructor_id']);
            $students = User::where('tenant_id', $tenantId)->whereHas('roles', fn($q) => $q->where('name', 'student'))->get();
            $categories = \App\Models\Category::where('tenant_id', $tenantId)->get();
            $tags = \App\Models\Tag::where('tenant_id', $tenantId)->get();

            foreach ($extraCourses[$tenantId] as $courseData) {
                $course = Course::create([
                    'title' => $courseData['title'],
                    'slug' => $courseData['slug'],
                    'description' => $courseData['description'],
                    'level' => $courseData['level'],
                    'price' => $courseData['price'],
                    'published' => $courseData['published'],
                    'tenant_id' => $tenantId,
                    'created_by' => $instructor->id,
                    'category_id' => $categories->random()->id,
                ]);

                $course->tags()->attach(
                    $tags->random(rand(2, 4))->pluck('id')->toArray()
                );

                if ($courseData['published']) {
                    foreach ($students as $student) {
                        Enrollment::create([
                            'user_id' => $student->id,
                            'course_id' => $course->id,
                            'status' => 'active',
                            'enrolled_at' => now(),
                        ]);
                    }

                    $reviewContents = [
                        'Great course! Very well structured and easy to follow.',
                        'Excellent content and practical examples.',
                        'Good introduction but could use more advanced topics.',
                        'Perfect for beginners. Highly recommended!',
                        'Learned a lot from this course. The instructor explains concepts clearly.',
                    ];
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

                        $questionsData = [
                            ['type' => 'multiple_choice', 'text' => 'Which of the following best describes this concept?', 'options' => json_encode(['Option A', 'Option B', 'Option C', 'Option D']), 'answer' => 'Option A'],
                            ['type' => 'multiple_choice', 'text' => 'What is the primary purpose of this technique?', 'options' => json_encode(['Performance', 'Security', 'Usability', 'Scalability']), 'answer' => 'Usability'],
                            ['type' => 'true_false', 'text' => 'This concept is always true in all scenarios.', 'options' => json_encode(['True', 'False']), 'answer' => 'False'],
                        ];

                        foreach ($questionsData as $qIdx => $qData) {
                            $question = Question::create([
                                'tenant_id' => $tenantId,
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

                        $gradebookItem = GradebookItem::create([
                            'course_id' => $course->id,
                            'title' => $quiz->title,
                            'type' => 'quiz',
                            'item_id' => $quiz->id,
                            'weight' => 50,
                            'max_score' => 30,
                        ]);

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

                        $gradebookItem = GradebookItem::create([
                            'course_id' => $course->id,
                            'title' => $assignment->title,
                            'type' => 'assignment',
                            'item_id' => $assignment->id,
                            'weight' => 50,
                            'max_score' => 100,
                        ]);

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
                }

                // Demo data
                $allLessons = Lesson::whereIn('section_id', Section::where('course_id', $course->id)->pluck('id'))
                    ->orderBy('section_id')->orderBy('order')->get();

                foreach ($allLessons as $lesson) {
                    $slug = str($lesson->title)->slug();
                    $tenantPrefix = 'tenants/' . $tenantId . '/materials/demo';
                    Material::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'title' => $lesson->title . ' - Notes'],
                        ['tenant_id' => $tenantId, 'file_path' => $tenantPrefix . '/' . $slug . '.pdf', 'file_type' => 'pdf', 'file_size' => 0]
                    );
                    Material::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'title' => $lesson->title . ' - Slide'],
                        ['tenant_id' => $tenantId, 'file_path' => $tenantPrefix . '/' . $slug . '.pptx', 'file_type' => 'pptx', 'file_size' => 0]
                    );
                }

                foreach ($allLessons as $lesson) {
                    Video::updateOrCreate(
                        ['lesson_id' => $lesson->id],
                        ['tenant_id' => $tenantId, 'title' => $lesson->title . ' - Video', 'original_path' => null, 'hls_path' => null, 'status' => 'ready', 'duration_seconds' => 0]
                    );
                }

                if ($courseData['published']) {
                    $lessonIds = $allLessons->pluck('id');

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

                    LiveClass::create([
                        'tenant_id' => $tenantId, 'course_id' => $course->id,
                        'title' => 'Kickoff Session: ' . $course->title,
                        'description' => 'Introduction and overview of the course',
                        'provider' => 'jitsi', 'start_time' => now()->subDays(rand(3, 7)),
                        'end_time' => now()->subDays(rand(2, 6))->addHours(1),
                        'status' => 'ended', 'created_by' => $instructor->id,
                    ]);
                    LiveClass::create([
                        'tenant_id' => $tenantId, 'course_id' => $course->id,
                        'title' => 'Live Q&A — ' . $course->title,
                        'description' => 'Live question and answer session',
                        'provider' => 'google_meet', 'start_time' => now()->addDays(rand(5, 14)),
                        'end_time' => now()->addDays(rand(5, 14))->addHours(1),
                        'status' => 'scheduled', 'created_by' => $instructor->id,
                    ]);
                }
            }
        }
    }
}
