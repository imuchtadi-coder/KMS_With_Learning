<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\BulkOperationController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\GamificationController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\KnowledgeSpaceController;
use App\Http\Controllers\KmsAnalyticsController;
use App\Http\Controllers\SmeDirectoryController;
use App\Http\Controllers\AssessmentAnalyticsController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\DiscussionReplyController;
use App\Http\Controllers\InstructorAssignmentController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorExamController;
use App\Http\Controllers\InstructorGradebookController;
use App\Http\Controllers\InstructorLiveClassController;
use App\Http\Controllers\InstructorQuestionController;
use App\Http\Controllers\InstructorQuizController;
use App\Http\Controllers\LearningPathAnalyticsController;
use App\Http\Controllers\LearningPathController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentCourseController;
use App\Http\Controllers\StudentCourseReviewController;
use App\Http\Controllers\StudentGamificationController;
use App\Http\Controllers\StudentGradeController;
use App\Http\Controllers\StudentLeaderboardController;
use App\Http\Controllers\StudentLearningPathController;
use App\Http\Controllers\StudentLiveClassController;
use App\Http\Controllers\StudentQuizController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Landing Page & Demo Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/demo/login', [DemoController::class, 'quickLogin'])->name('demo.login');

// Tenant Self-Registration (Pending Super Admin Approval)
Route::get('/register-tenant', [TenantRegistrationController::class, 'create'])->name('register-tenant');
Route::post('/register-tenant', [TenantRegistrationController::class, 'store'])->name('register-tenant.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Dashboard route - redirect based on role
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');

    // User Profile routes
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Admin routes (superadmin + tenant_admin)
    Route::middleware('role:superadmin|tenant_admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // User management
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
        Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

        // Course approval & publishing (tenant admin and superadmin)
        Route::middleware('role:tenant_admin|superadmin')->group(function () {
            Route::get('/admin/courses', [AdminController::class, 'courses'])->name('admin.courses.index');
            Route::get('/admin/courses/{course}', [AdminController::class, 'showCourse'])->name('admin.courses.show');
            Route::post('/admin/courses/{course}/publish', [AdminController::class, 'publishCourse'])->name('admin.courses.publish');
            Route::post('/admin/courses/{course}/unpublish', [AdminController::class, 'unpublishCourse'])->name('admin.courses.unpublish');

            // Learning Paths Management
            Route::get('/admin/assessment-analytics', [AssessmentAnalyticsController::class, 'tenant'])->name('admin.assessment-analytics.tenant');
            Route::get('/admin/learning-paths', [LearningPathController::class, 'index'])->name('admin.learning-paths.index');
            Route::get('/admin/learning-paths/create', [LearningPathController::class, 'create'])->name('admin.learning-paths.create');
            Route::post('/admin/learning-paths', [LearningPathController::class, 'store'])->name('admin.learning-paths.store');
            Route::get('/admin/learning-paths/{learningPath}', [LearningPathController::class, 'show'])->name('admin.learning-paths.show');
            Route::get('/admin/learning-paths/{learningPath}/edit', [LearningPathController::class, 'edit'])->name('admin.learning-paths.edit');
            Route::put('/admin/learning-paths/{learningPath}', [LearningPathController::class, 'update'])->name('admin.learning-paths.update');
            Route::delete('/admin/learning-paths/{learningPath}', [LearningPathController::class, 'destroy'])->name('admin.learning-paths.destroy');
            Route::post('/admin/learning-paths/{learningPath}/courses', [LearningPathController::class, 'addCourse'])->name('admin.learning-paths.add-course');
            Route::delete('/admin/learning-paths/{learningPath}/courses/{course}', [LearningPathController::class, 'removeCourse'])->name('admin.learning-paths.remove-course');
            Route::post('/admin/learning-paths/{learningPath}/courses/{course}/reorder', [LearningPathController::class, 'reorderCourse'])->name('admin.learning-paths.courses.reorder');
            Route::post('/admin/learning-paths/{learningPath}/publish', [LearningPathController::class, 'publish'])->name('admin.learning-paths.publish');
            Route::post('/admin/learning-paths/{learningPath}/unpublish', [LearningPathController::class, 'unpublish'])->name('admin.learning-paths.unpublish');

            // Learning Paths Analytics
            Route::get('/admin/learning-paths-analytics', [LearningPathAnalyticsController::class, 'dashboard'])->name('admin.learning-paths.analytics.dashboard');
            Route::get('/admin/learning-paths/{learningPath}/analytics', [LearningPathAnalyticsController::class, 'pathAnalytics'])->name('admin.learning-paths.analytics.path');
        });

        // Mail Settings (tenant admin & superadmin)
        Route::get('/admin/mail-settings', [AdminController::class, 'mailSettings'])->name('admin.mail-settings');
        Route::put('/admin/mail-settings', [AdminController::class, 'updateMailSettings'])->name('admin.mail-settings.update');
        Route::post('/admin/mail-settings/test', [AdminController::class, 'testMailSettings'])->name('admin.mail-settings.test');

        // Branding & Layanan CS (tenant admin & superadmin)
        Route::middleware('role:tenant_admin|superadmin')->group(function () {
            Route::get('/admin/branding', [AdminController::class, 'branding'])->name('admin.branding');
            Route::put('/admin/branding', [AdminController::class, 'updateBranding'])->name('admin.branding.update');
        });

        // Categories & Tags
        Route::resource('/admin/categories', CategoryController::class)->names('admin.categories')->except(['show']);
        Route::resource('/admin/tags', TagController::class)->names('admin.tags')->except(['show']);

        // Audit Logs
        Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
        Route::get('/admin/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('admin.audit-logs.show');

        // Badges
        Route::resource('/admin/badges', BadgeController::class)->names('admin.badges')->except(['show']);

        // Gamification Settings (XP, Levels, Streaks)
        Route::get('/admin/gamification', [GamificationController::class, 'index'])->name('admin.gamification.index');
        Route::post('/admin/gamification/xp', [GamificationController::class, 'updateXp'])->name('admin.gamification.update-xp');
        Route::post('/admin/gamification/xp/reset', [GamificationController::class, 'resetXp'])->name('admin.gamification.reset-xp');
        Route::post('/admin/gamification/levels', [GamificationController::class, 'updateLevels'])->name('admin.gamification.update-levels');
        Route::post('/admin/gamification/settings', [GamificationController::class, 'updateSettings'])->name('admin.gamification.update-settings');
        Route::post('/admin/gamification/settings/reset', [GamificationController::class, 'resetSettings'])->name('admin.gamification.reset-settings');

        // Bulk Operations
        Route::get('/admin/bulk/import-users', [BulkOperationController::class, 'importUsersForm'])->name('admin.bulk.import-users');
        Route::post('/admin/bulk/import-users', [BulkOperationController::class, 'importUsers']);
        Route::get('/admin/bulk/enroll', [BulkOperationController::class, 'bulkEnrollForm'])->name('admin.bulk.enroll');
        Route::post('/admin/bulk/enroll', [BulkOperationController::class, 'bulkEnroll']);
        Route::get('/admin/bulk/certificates', [BulkOperationController::class, 'bulkCertificatesForm'])->name('admin.bulk.certificates');
        Route::post('/admin/bulk/certificates', [BulkOperationController::class, 'bulkCertificates']);
        Route::get('/admin/bulk/enrolled-students', [BulkOperationController::class, 'enrolledStudents'])->name('admin.bulk.enrolled-students');
    });

    // Superadmin only routes
    Route::middleware('role:superadmin')->group(function () {
        // Tenant management
        Route::get('/admin/tenants', [AdminController::class, 'tenants'])->name('admin.tenants.index');
        Route::get('/admin/tenants/create', [AdminController::class, 'createTenant'])->name('admin.tenants.create');
        Route::post('/admin/tenants', [AdminController::class, 'storeTenant'])->name('admin.tenants.store');
        Route::post('/admin/tenants/{tenant}/approve', [AdminController::class, 'approveTenant'])->name('admin.tenants.approve');
        Route::post('/admin/tenants/{tenant}/reject', [AdminController::class, 'rejectTenant'])->name('admin.tenants.reject');
        Route::get('/admin/tenants/{tenant}/edit', [AdminController::class, 'editTenant'])->name('admin.tenants.edit');
        Route::put('/admin/tenants/{tenant}', [AdminController::class, 'updateTenant'])->name('admin.tenants.update');
        Route::delete('/admin/tenants/{tenant}', [AdminController::class, 'deleteTenant'])->name('admin.tenants.delete');

        // Subscription Plans & Pricing management (Superadmin)
        Route::get('/admin/subscription-plans', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'index'])->name('admin.subscription-plans.index');
        Route::post('/admin/subscription-plans', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'store'])->name('admin.subscription-plans.store');
        Route::put('/admin/subscription-plans/{plan}', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'update'])->name('admin.subscription-plans.update');
        Route::delete('/admin/subscription-plans/{plan}', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'destroy'])->name('admin.subscription-plans.destroy');
        Route::put('/admin/payment-methods/{paymentMethod}', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'updatePaymentMethod'])->name('admin.payment-methods.update');
    });

    // Instructor routes
    Route::middleware('role:instructor')->group(function () {
        Route::get('/instructor', [InstructorCourseController::class, 'dashboard'])->name('instructor.dashboard');
        Route::get('/instructor/courses', [InstructorCourseController::class, 'index'])->name('instructor.courses.index');
        Route::get('/instructor/courses/create', [InstructorCourseController::class, 'create'])->name('instructor.courses.create');
        Route::post('/instructor/courses', [InstructorCourseController::class, 'store'])->name('instructor.courses.store');
        Route::get('/instructor/courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('instructor.courses.edit');
        Route::put('/instructor/courses/{course}', [InstructorCourseController::class, 'update'])->name('instructor.courses.update');
        Route::delete('/instructor/courses/{course}', [InstructorCourseController::class, 'destroy'])->name('instructor.courses.destroy');

        // Sections
        Route::post('/instructor/courses/{course}/sections', [InstructorCourseController::class, 'storeSection'])->name('instructor.sections.store');
        Route::put('/instructor/sections/{section}', [InstructorCourseController::class, 'updateSection'])->name('instructor.sections.update');
        Route::delete('/instructor/sections/{section}', [InstructorCourseController::class, 'destroySection'])->name('instructor.sections.destroy');

        // Lessons
        Route::post('/instructor/sections/{section}/lessons', [InstructorCourseController::class, 'storeLesson'])->name('instructor.lessons.store');
        Route::put('/instructor/lessons/{lesson}', [InstructorCourseController::class, 'updateLesson'])->name('instructor.lessons.update');
        Route::delete('/instructor/lessons/{lesson}', [InstructorCourseController::class, 'destroyLesson'])->name('instructor.lessons.destroy');        
        Route::post('/instructor/sections/{section}/lessons/reorder', [InstructorCourseController::class, 'reorderLessons'])->name('instructor.lessons.reorder');

        // Videos
        Route::post('/instructor/lessons/{lesson}/videos', [VideoController::class, 'store'])->name('instructor.videos.store');
        Route::get('/instructor/lessons/{lesson}/videos', [VideoController::class, 'getForLesson'])->name('instructor.videos.list');
        Route::delete('/instructor/videos/{video}', [VideoController::class, 'destroy'])->name('instructor.videos.destroy');

        // Materials
        Route::post('/instructor/lessons/{lesson}/materials', [MaterialController::class, 'store'])->name('instructor.materials.store');
        Route::delete('/instructor/materials/{material}', [MaterialController::class, 'destroy'])->name('instructor.materials.destroy');
        Route::get('/instructor/materials/{material}/preview', [MaterialController::class, 'instructorPreview'])->name('instructor.materials.preview');
        Route::get('/instructor/materials/{material}/download', [MaterialController::class, 'instructorDownload'])->name('instructor.materials.download');

        // Materials API (for modal)
        Route::get('/instructor/lessons/{lesson}/data', [MaterialController::class, 'getLessonData'])->name('instructor.lessons.data');
        Route::get('/instructor/lessons/{lesson}/materials', [MaterialController::class, 'getLessonMaterials'])->name('instructor.lessons.materials');

        // Course Detail, Preview, Progress
        Route::get('/instructor/courses/{course}/preview', [InstructorCourseController::class, 'preview'])->name('instructor.courses.preview');
        Route::get('/instructor/courses/{course}/progress', [InstructorCourseController::class, 'progress'])->name('instructor.courses.progress');
        Route::get('/instructor/courses/{course}', [InstructorCourseController::class, 'show'])->name('instructor.courses.show');

        // Question Bank
        Route::get('/instructor/courses/{course}/questions', [InstructorQuestionController::class, 'index'])->name('instructor.questions.index');
        Route::get('/instructor/courses/{course}/questions/create', [InstructorQuestionController::class, 'create'])->name('instructor.questions.create');
        Route::post('/instructor/courses/{course}/questions', [InstructorQuestionController::class, 'store'])->name('instructor.questions.store');
        Route::get('/instructor/courses/{course}/questions/{question}/edit', [InstructorQuestionController::class, 'edit'])->name('instructor.questions.edit');
        Route::put('/instructor/courses/{course}/questions/{question}', [InstructorQuestionController::class, 'update'])->name('instructor.questions.update');
        Route::delete('/instructor/courses/{course}/questions/{question}', [InstructorQuestionController::class, 'destroy'])->name('instructor.questions.destroy');

        // Quizzes
        Route::get('/instructor/courses/{course}/quizzes', [InstructorQuizController::class, 'index'])->name('instructor.quizzes.index');
        Route::get('/instructor/courses/{course}/quizzes-grading', [InstructorQuizController::class, 'grading'])->name('instructor.courses.quiz-grading');
        Route::get('/instructor/courses/{course}/quizzes/{quiz}/attempts', [InstructorQuizController::class, 'attempts'])->name('instructor.quizzes.attempts');
        Route::get('/instructor/courses/{course}/quizzes/create', [InstructorQuizController::class, 'create'])->name('instructor.quizzes.create');
        Route::post('/instructor/courses/{course}/quizzes', [InstructorQuizController::class, 'store'])->name('instructor.quizzes.store');
        Route::get('/instructor/courses/{course}/quizzes/{quiz}', [InstructorQuizController::class, 'show'])->name('instructor.quizzes.show');
        Route::get('/instructor/courses/{course}/quizzes/{quiz}/edit', [InstructorQuizController::class, 'edit'])->name('instructor.quizzes.edit');
        Route::put('/instructor/courses/{course}/quizzes/{quiz}', [InstructorQuizController::class, 'update'])->name('instructor.quizzes.update');
        Route::delete('/instructor/courses/{course}/quizzes/{quiz}', [InstructorQuizController::class, 'destroy'])->name('instructor.quizzes.destroy');
        Route::post('/instructor/courses/{course}/quizzes/{quiz}/publish', [InstructorQuizController::class, 'publish'])->name('instructor.quizzes.publish');
        Route::post('/instructor/courses/{course}/quizzes/{quiz}/unpublish', [InstructorQuizController::class, 'unpublish'])->name('instructor.quizzes.unpublish');
        Route::get('/instructor/courses/{course}/quizzes/{quiz}/attempts/{attempt}/essay-review', [InstructorQuizController::class, 'essayReview'])->name('instructor.quizzes.essay-review');
        Route::post('/instructor/courses/{course}/quizzes/{quiz}/attempts/{attempt}/essay-grade', [InstructorQuizController::class, 'gradeEssay'])->name('instructor.quizzes.essay-grade');

        // Exams
        Route::get('/instructor/courses/{course}/exams/{quiz}/monitor', [InstructorExamController::class, 'monitor'])->name('instructor.exams.monitor');
        Route::get('/instructor/courses/{course}/exams/{quiz}/sessions', [InstructorExamController::class, 'sessions'])->name('instructor.exams.sessions');
        Route::post('/instructor/courses/{course}/exams/{quiz}/force-submit/{attempt}', [InstructorExamController::class, 'forceSubmit'])->name('instructor.exams.force-submit');

        // Instructor Assignments
        Route::get('/instructor/courses/{course}/assignments', [InstructorAssignmentController::class, 'index'])->name('instructor.assignments.index');
        Route::get('/instructor/courses/{course}/assignments/create', [InstructorAssignmentController::class, 'create'])->name('instructor.assignments.create');
        Route::post('/instructor/courses/{course}/assignments', [InstructorAssignmentController::class, 'store'])->name('instructor.assignments.store');
        Route::get('/instructor/courses/{course}/assignments/{assignment}', [InstructorAssignmentController::class, 'show'])->name('instructor.assignments.show');
        Route::get('/instructor/courses/{course}/assignments/{assignment}/edit', [InstructorAssignmentController::class, 'edit'])->name('instructor.assignments.edit');
        Route::put('/instructor/courses/{course}/assignments/{assignment}', [InstructorAssignmentController::class, 'update'])->name('instructor.assignments.update');
        Route::delete('/instructor/courses/{course}/assignments/{assignment}', [InstructorAssignmentController::class, 'destroy'])->name('instructor.assignments.destroy');
        Route::get('/instructor/courses/{course}/assignments/{assignment}/submissions/{submission}/download', [InstructorAssignmentController::class, 'downloadSubmission'])->name('instructor.assignments.submissions.download');
        Route::get('/instructor/courses/{course}/assignments/{assignment}/submissions/{submission}/preview', [InstructorAssignmentController::class, 'previewSubmission'])->name('instructor.assignments.submissions.preview');
        Route::get('/instructor/courses/{course}/assignments/{assignment}/submissions/{submission}/grade', [InstructorAssignmentController::class, 'gradeForm'])->name('instructor.assignments.submissions.grade');
        Route::post('/instructor/courses/{course}/assignments/{assignment}/submissions/{submission}/grade', [InstructorAssignmentController::class, 'grade'])->name('instructor.assignments.submissions.grade.store');

        // Gradebook
        Route::get('/instructor/courses/{course}/gradebook', [InstructorGradebookController::class, 'index'])->name('instructor.gradebook.index');
        Route::get('/instructor/courses/{course}/gradebook/export-csv', [InstructorGradebookController::class, 'exportCsv'])->name('instructor.gradebook.export-csv');
        Route::put('/instructor/courses/{course}/gradebook/update-grade', [InstructorGradebookController::class, 'updateGrade'])->name('instructor.gradebook.update-grade');

        // Assessment Analytics (instructor + tenant_admin)
        Route::get('/instructor/assessment-analytics', [AssessmentAnalyticsController::class, 'instructorDashboard'])->name('instructor.assessment-analytics.dashboard');
        Route::get('/instructor/courses/{course}/assessment-analytics', [AssessmentAnalyticsController::class, 'course'])->name('instructor.assessment-analytics.course');
        Route::get('/instructor/courses/{course}/assessment-analytics/export-csv', [AssessmentAnalyticsController::class, 'exportCsv'])->name('instructor.assessment-analytics.export-csv');

        // Live Classes
        Route::get('/instructor/courses/{course}/live-classes', [InstructorLiveClassController::class, 'index'])->name('instructor.live-classes.index');
        Route::get('/instructor/courses/{course}/live-classes/create', [InstructorLiveClassController::class, 'create'])->name('instructor.live-classes.create');
        Route::post('/instructor/courses/{course}/live-classes', [InstructorLiveClassController::class, 'store'])->name('instructor.live-classes.store');
        Route::get('/instructor/courses/{course}/live-classes/{liveClass}/edit', [InstructorLiveClassController::class, 'edit'])->name('instructor.live-classes.edit');
        Route::put('/instructor/courses/{course}/live-classes/{liveClass}', [InstructorLiveClassController::class, 'update'])->name('instructor.live-classes.update');
        Route::delete('/instructor/courses/{course}/live-classes/{liveClass}', [InstructorLiveClassController::class, 'destroy'])->name('instructor.live-classes.destroy');
        Route::post('/instructor/courses/{course}/live-classes/{liveClass}/start', [InstructorLiveClassController::class, 'startMeeting'])->name('instructor.live-classes.start');
        Route::post('/instructor/courses/{course}/live-classes/{liveClass}/end', [InstructorLiveClassController::class, 'endMeeting'])->name('instructor.live-classes.end');

        // Certificates Aproval
        Route::post('/instructor/courses/{course}/enrollments/{enrollment}/certificate', [InstructorCourseController::class, 'approveCertificate'])->name('instructor.courses.enrollments.certificate');
    });

    // Notification routes (all authenticated users)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    });

    // Search routes (all authenticated users)
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // Knowledge Base Spaces, Articles & Analytics routes (all authenticated users)
    Route::get('/kms/analytics', [KmsAnalyticsController::class, 'index'])->name('kms.analytics');
    Route::resource('spaces', KnowledgeSpaceController::class);
    Route::get('/articles/attachments/{attachment}/download', [ArticleController::class, 'downloadAttachment'])->name('articles.attachments.download');
    Route::delete('/articles/attachments/{attachment}', [ArticleController::class, 'deleteAttachment'])->name('articles.attachments.destroy');
    Route::post('/articles/{article}/submit-review', [ArticleController::class, 'submitReview'])->name('articles.submit-review');
    Route::post('/articles/{article}/approve', [ArticleController::class, 'approve'])->name('articles.approve');
    Route::post('/articles/{article}/reject', [ArticleController::class, 'reject'])->name('articles.reject');
    Route::post('/articles/{article}/versions/{version}/restore', [ArticleController::class, 'restoreVersion'])->name('articles.versions.restore');
    Route::resource('articles', ArticleController::class);

    // Discussion routes (all authenticated users)
    Route::resource('discussions', DiscussionController::class)->except(['show']);
    Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/discussions/{discussion}/pin', [DiscussionController::class, 'pin'])->name('discussions.pin');
    Route::get('/lessons/{lesson}/discussions', [DiscussionController::class, 'lessonDiscussions'])->name('lessons.discussions');

    // Discussion Reply routes
    Route::post('/discussions/{discussion}/replies', [DiscussionReplyController::class, 'store'])->name('discussions.replies.store');
    Route::put('/discussions/{discussion}/replies/{reply}', [DiscussionReplyController::class, 'update'])->name('discussions.replies.update');
    Route::delete('/discussions/{discussion}/replies/{reply}', [DiscussionReplyController::class, 'destroy'])->name('discussions.replies.destroy');
    Route::post('/discussions/{discussion}/replies/{reply}/solution', [DiscussionReplyController::class, 'toggleSolution'])->name('discussions.replies.solution');
    Route::get('/discussions-check-similar', [DiscussionController::class, 'checkSimilar'])->name('discussions.check-similar');

    // SME (Subject Matter Expert) Directory routes
    Route::get('/experts', [SmeDirectoryController::class, 'index'])->name('experts.index');
    Route::post('/experts/skills/{userSkill}/endorse', [SmeDirectoryController::class, 'endorse'])->name('experts.endorse');
    Route::post('/experts/skills/add', [SmeDirectoryController::class, 'addSkill'])->name('experts.skills.add');
    Route::delete('/experts/skills/{userSkill}', [SmeDirectoryController::class, 'deleteSkill'])->name('experts.skills.destroy');
    Route::post('/experts/skills/{userSkill}/verify', [SmeDirectoryController::class, 'verifySkill'])->name('experts.skills.verify');

    // Student routes
    Route::middleware([\App\Http\Middleware\InitializeTenancyByDomainOrAuthenticatedTenant::class, 'role:student',])->group(function () {
        Route::get('/student', [StudentCourseController::class, 'dashboard'])
            ->name('student.dashboard');

        Route::get('/student/courses', [StudentCourseController::class, 'listCourses'])
            ->name('student.courses.list');

        Route::get('/student/courses/{course}', [StudentCourseController::class, 'showCourse'])
            ->name('student.courses.show');

        Route::post('/student/courses/{course}/enroll', [StudentCourseController::class, 'enroll'])
            ->name('student.courses.enroll');

        Route::get('/student/enrollments', [StudentCourseController::class, 'myEnrollments'])
            ->name('student.enrollments');

        Route::get('/student/enrollments/{enrollment}', [StudentCourseController::class, 'showEnrollment'])
            ->name('student.enrollments.show');

        Route::get('/student/courses/{course}/lessons/{lesson}', [StudentCourseController::class, 'viewLesson'])
            ->name('student.lessons.view');

        Route::post('/student/lessons/{lesson}/complete', [StudentCourseController::class, 'completeLesson'])
            ->name('student.lessons.complete');

        Route::post('/student/lessons/update-progress', [ProgressController::class, 'update'])
            ->name('student.lessons.update-progress');

        // Learning Paths
        Route::get('/student/learning-paths', [StudentLearningPathController::class, 'index'])->name('student.learning-paths.index');
        Route::get('/student/learning-paths/my-paths', [StudentLearningPathController::class, 'myPaths'])->name('student.learning-paths.my-paths');
        Route::get('/student/learning-paths/{learningPath}', [StudentLearningPathController::class, 'show'])->name('student.learning-paths.show');
        Route::post('/student/learning-paths/{learningPath}/enroll', [StudentLearningPathController::class, 'enroll'])->name('student.learning-paths.enroll');
        Route::post('/student/learning-paths/{learningPath}/courses/{course}/complete', [StudentLearningPathController::class, 'completeCourse'])->name('student.learning-paths.complete-course');
        Route::get('/student/learning-paths/{learningPath}/next-course', [StudentLearningPathController::class, 'getNextCourse'])->name('student.learning-paths.next-course');

        // Materials
        Route::get('/student/materials/{material}/download', [MaterialController::class, 'download'])->name('student.materials.download');
        Route::get('/student/materials/{material}/preview', [MaterialController::class, 'preview'])->name('student.materials.preview');

        // Certificates
        Route::get('/student/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('student.certificates.download');
        Route::get('/student/certificates/{certificate}/view', [CertificateController::class, 'view'])->name('student.certificates.view');

        // Quizzes
        Route::get('/student/quizzes/{quiz}', [StudentQuizController::class, 'show'])->name('student.quizzes.show');
        Route::post('/student/quizzes/{quiz}/start', [StudentQuizController::class, 'start'])->name('student.quizzes.start');
        Route::get('/student/quiz-attempts/{attempt}/take', [StudentQuizController::class, 'take'])->name('student.quizzes.take');
        Route::post('/student/quiz-attempts/{attempt}/submit', [StudentQuizController::class, 'submit'])->name('student.quizzes.submit');
        Route::get('/student/quiz-attempts/{attempt}/result', [StudentQuizController::class, 'result'])->name('student.quizzes.result');

        // Student Assignments
        Route::get('/student/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('student.assignments.show');
        Route::get('/student/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submitForm'])->name('student.assignments.submit-form');
        Route::post('/student/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');

        // Grades
        Route::get('/student/grades', [StudentGradeController::class, 'index'])->name('student.grades.index');
        Route::get('/student/grades/courses/{course}', [StudentGradeController::class, 'course'])->name('student.grades.course');
        Route::get('/student/grades/courses/{course}/transcript', [StudentGradeController::class, 'transcript'])->name('student.grades.transcript');

        // Live Classes
        Route::get('/student/live-classes', [StudentLiveClassController::class, 'index'])->name('student.live-classes.index');
        Route::get('/student/live-classes/{liveClass}/join', [StudentLiveClassController::class, 'join'])->name('student.live-classes.join');

        // Reviews
        Route::post('/student/courses/{course}/reviews', [StudentCourseReviewController::class, 'store'])->name('student.reviews.store');
        Route::put('/student/courses/{course}/reviews/{review}', [StudentCourseReviewController::class, 'update'])->name('student.reviews.update');
        Route::delete('/student/courses/{course}/reviews/{review}', [StudentCourseReviewController::class, 'destroy'])->name('student.reviews.destroy');

        // Gamification
        Route::get('/student/gamification', [StudentGamificationController::class, 'index'])->name('student.gamification.index');
        Route::get('/student/leaderboard', [StudentLeaderboardController::class, 'index'])->name('student.leaderboard.index');
    });
});

// Test Inertia page
Route::middleware(['web'])->get('/inertia-test', function () {
    return Inertia::render('Welcome');
});
