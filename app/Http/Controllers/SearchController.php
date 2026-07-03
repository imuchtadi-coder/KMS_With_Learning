<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $type = $request->input('type', 'all');
        $user = auth()->user();

        if (!$q || strlen(trim($q)) < 2) {
            return Inertia::render('Search/Index', ['q' => $q, 'results' => collect(), 'type' => $type]);
        }

        $searchTerm = trim($q);
        $tenantId = $user->tenant_id;

        $results = collect();

        if ($type === 'all' || $type === 'courses') {
            $courses = Course::where('tenant_id', $tenantId)
                ->where('published', true)
                ->where(function ($query) use ($searchTerm) {
                    $query->whereRaw("LOWER(title) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
                        ->orWhereRaw("LOWER(description) LIKE ?", ['%' . strtolower($searchTerm) . '%']);
                })
                ->get()
                ->map(fn($c) => ['type' => 'course', 'item' => $c, 'url' => $user->hasRole('student') ? route('student.courses.show', $c) : route('instructor.courses.show', $c)]);
            $results = $results->concat($courses);
        }

        if ($type === 'all' || $type === 'lessons') {
            $lessons = Lesson::whereHas('section.course', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->where('published', true);
            })
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw("LOWER(title) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw("LOWER(description) LIKE ?", ['%' . strtolower($searchTerm) . '%']);
            })
            ->with('section.course')
            ->get()
            ->map(fn($l) => ['type' => 'lesson', 'item' => $l, 'url' => $user->hasRole('student') ? route('student.lessons.view', ['course' => $l->section->course, 'lesson' => $l]) : '#']);
            $results = $results->concat($lessons);
        }

        if ($type === 'all' || $type === 'users') {
            if ($user->hasRole(['superadmin', 'tenant_admin'])) {
                $users = User::where('tenant_id', $tenantId)
                    ->where(function ($query) use ($searchTerm) {
                        $query->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
                            ->orWhereRaw("LOWER(email) LIKE ?", ['%' . strtolower($searchTerm) . '%']);
                    })
                    ->get()
                    ->map(fn($u) => ['type' => 'user', 'item' => $u, 'url' => route('admin.users.edit', $u)]);
                $results = $results->concat($users);
            }
        }

        $results = $results->sortByDesc(fn($r) => $r['item']->created_at ?? now());

        return Inertia::render('Search/Index', compact('results', 'q', 'type'));
    }
}
