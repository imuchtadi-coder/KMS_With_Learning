<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReview;
use App\Http\Requests\StoreCourseReviewRequest;
use App\Http\Requests\UpdateCourseReviewRequest;
use Illuminate\Http\Request;

class StudentCourseReviewController extends Controller
{
    public function store(StoreCourseReviewRequest $request, Course $course)
    {
        $user = auth()->user();

        $enrolled = $course->enrollments()->where('user_id', $user->id)->exists();
        if (!$enrolled) abort(403, 'You must be enrolled to review this course.');

        $validated = $request->validated();

        $review = CourseReview::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $user->id],
            ['rating' => $validated['rating'], 'review' => $validated['review'] ?? null]
        );

        $course->recalculateRating();

        return redirect()->back()->with('success', 'Review submitted successfully!');
    }

    public function update(UpdateCourseReviewRequest $request, Course $course, CourseReview $review)
    {
        $user = auth()->user();
        if ($review->user_id !== $user->id) abort(403);

        $validated = $request->validated();

        $review->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]);

        $course->recalculateRating();

        return redirect()->back()->with('success', 'Review updated successfully!');
    }

    public function destroy(Course $course, CourseReview $review)
    {
        $user = auth()->user();
        if ($review->user_id !== $user->id) abort(403);

        $review->delete();
        $course->recalculateRating();

        return redirect()->back()->with('success', 'Review removed successfully!');
    }
}
