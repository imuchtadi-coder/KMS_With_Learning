<?php

namespace App\Http\Controllers;

use App\Models\LiveClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentLiveClassController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $liveClasses = LiveClass::whereHas('course.enrollments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['course', 'creator'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        $upcoming = LiveClass::whereHas('course.enrollments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('start_time', '>', now())
            ->with('course')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return Inertia::render('Student/LiveClasses/Index', [
            'liveClasses' => $liveClasses,
            'upcoming' => $upcoming,
        ]);
    }

    public function join(LiveClass $liveClass)
    {
        $user = auth()->user();

        $enrolled = $liveClass->course->enrollments()
            ->where('user_id', $user->id)
            ->exists();

        if (!$enrolled) abort(403);

        $liveClass->load('course');

        return Inertia::render('Student/LiveClasses/Join', [
            'liveClass' => $liveClass,
        ]);
    }
}
