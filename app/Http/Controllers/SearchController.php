<?php

namespace App\Http\Controllers;

use App\Models\Article;
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
        $user = $request->user() ?: auth()->user();

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

        if ($type === 'all' || $type === 'articles') {
            $articlesQuery = Article::query();
            if (!$user->hasRole('superadmin')) {
                $articlesQuery->where('tenant_id', $tenantId);
            }
            if (!$user->hasRole(['superadmin', 'tenant_admin'])) {
                $articlesQuery->where(function ($q) use ($user) {
                    $q->where('status', 'published')->orWhere('author_id', $user->id);
                });
            }

            $articles = $articlesQuery->where(function ($query) use ($searchTerm) {
                $query->whereRaw("search_vector @@ websearch_to_tsquery('simple', ?)", [$searchTerm])
                    ->orWhereRaw("LOWER(title) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw("LOWER(excerpt) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw("LOWER(content) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereHas('attachments', function ($attQuery) use ($searchTerm) {
                        $attQuery->whereRaw("search_vector @@ websearch_to_tsquery('simple', ?)", [$searchTerm])
                                 ->orWhereRaw("LOWER(extracted_text) LIKE ?", ['%' . strtolower($searchTerm) . '%']);
                    });
            })
            ->selectRaw("
                articles.*,
                ts_rank_cd(search_vector, websearch_to_tsquery('simple', ?)) as rank,
                ts_headline(
                    'simple',
                    regexp_replace(articles.content, '<[^>]*>', ' ', 'g'),
                    websearch_to_tsquery('simple', ?),
                    'StartSel=<mark>, StopSel=</mark>, MaxWords=35, MinWords=15, HighlightAll=false'
                ) as search_snippet
            ", [$searchTerm, $searchTerm])
            ->with(['category', 'tags', 'author', 'space', 'attachments'])
            ->orderByDesc('rank')
            ->latest('published_at')
            ->get()
            ->map(function ($a) use ($searchTerm) {
                // Check if match was found inside attachment
                $matchingAttachment = $a->attachments->first(function ($att) use ($searchTerm) {
                    return str_contains(strtolower($att->extracted_text ?? ''), strtolower($searchTerm))
                        || str_contains(strtolower($att->title ?? ''), strtolower($searchTerm));
                });

                $snippet = $a->search_snippet ?: $a->excerpt;
                if ($matchingAttachment) {
                    $snippet = "📄 [Ditemukan di dalam berkas {$matchingAttachment->title}.{$matchingAttachment->file_type}] " . $snippet;
                }

                return [
                    'type' => 'article',
                    'item' => $a,
                    'snippet' => $snippet,
                    'matched_attachment' => $matchingAttachment ? $matchingAttachment->title : null,
                    'url' => route('articles.show', $a),
                ];
            });

            $results = $results->concat($articles);
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
