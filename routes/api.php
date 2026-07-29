<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check (no auth required)
Route::get('/health', HealthController::class);

// Public authentication routes (rate limited)
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');
// Route::post('/auth/register', [AuthController::class, 'register']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Auth endpoints
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Course endpoints
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);
    Route::get('/my-enrollments', [CourseController::class, 'myEnrollments']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);

    // Video endpoints
    Route::post('/videos/upload', [VideoController::class, 'upload']);
    Route::get('/videos/{video}/play', [VideoController::class, 'play']);
    Route::get('/videos/{video}/status', [VideoController::class, 'status']);

    // Progress endpoints
    Route::post('/progress', [ProgressController::class, 'update']);
    Route::get('/courses/{courseId}/progress', [ProgressController::class, 'getCourseProgress']);
});
