<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Discussion;
use App\Models\KnowledgeSpace;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KmsAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        // 1. SOP & Documentation Health Matrix
        $articlesQuery = Article::query();
        if (!$user->hasRole('superadmin')) {
            $articlesQuery->where('tenant_id', $tenantId);
        }

        $totalArticles = (clone $articlesQuery)->count();
        $publishedArticles = (clone $articlesQuery)->where('status', 'published')->count();
        $pendingArticles = (clone $articlesQuery)->where('status', 'pending_review')->count();
        $draftArticles = (clone $articlesQuery)->where('status', 'draft')->count();

        // Document Freshness
        $expiredArticles = (clone $articlesQuery)->where('status', 'published')
            ->whereNotNull('next_review_at')
            ->where('next_review_at', '<', now())
            ->count();

        $expiringSoonArticles = (clone $articlesQuery)->where('status', 'published')
            ->whereNotNull('next_review_at')
            ->whereBetween('next_review_at', [now(), now()->addDays(14)])
            ->count();

        $healthyArticles = max(0, $publishedArticles - $expiredArticles - $expiringSoonArticles);

        // Space Breakdown
        $spaceBreakdown = KnowledgeSpace::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId))
            ->withCount(['articles as total_articles'])
            ->orderByDesc('total_articles')
            ->get(['id', 'name', 'color', 'icon', 'total_articles']);

        // 2. Q&A Problem-Solving Metrics
        $discussionsQuery = Discussion::query();
        if (!$user->hasRole('superadmin')) {
            $discussionsQuery->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereHas('course', fn ($c) => $c->where('tenant_id', $tenantId));
            });
        }

        $totalDiscussions = (clone $discussionsQuery)->count();
        $resolvedDiscussions = (clone $discussionsQuery)->where('is_resolved', true)->count();
        $unresolvedDiscussions = (clone $discussionsQuery)->where('is_resolved', false)->count();
        $resolutionRate = $totalDiscussions > 0 ? round(($resolvedDiscussions / $totalDiscussions) * 100, 1) : 0;

        // 3. SME & Contributor Leaderboard
        $topAuthors = User::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId))
            ->whereHas('articles', fn($q) => $q->where('status', 'published'))
            ->withCount(['articles as published_articles_count' => fn($q) => $q->where('status', 'published')])
            ->orderByDesc('published_articles_count')
            ->limit(5)
            ->get(['id', 'name', 'avatar', 'department']);

        $topSkills = Skill::query()
            ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->withCount('userSkills')
            ->orderByDesc('user_skills_count')
            ->limit(6)
            ->get(['id', 'name', 'category', 'user_skills_count']);

        // 4. Content Gap Detection (Unresolved questions with high activity that lack formal SOP)
        $contentGaps = (clone $discussionsQuery)
            ->where('is_resolved', false)
            ->withCount('replies')
            ->orderByDesc('replies_count')
            ->limit(5)
            ->get(['id', 'title', 'created_at', 'replies_count']);

        // Documents needing review immediately
        $urgentReviewArticles = (clone $articlesQuery)
            ->where('status', 'published')
            ->whereNotNull('next_review_at')
            ->where('next_review_at', '<=', now()->addDays(14))
            ->with(['author:id,name', 'space:id,name'])
            ->orderBy('next_review_at')
            ->limit(5)
            ->get(['id', 'title', 'version', 'next_review_at', 'author_id', 'space_id']);

        return Inertia::render('Kms/Analytics', [
            'docMetrics' => [
                'total' => $totalArticles,
                'published' => $publishedArticles,
                'pending' => $pendingArticles,
                'draft' => $draftArticles,
                'healthy' => $healthyArticles,
                'expiring_soon' => $expiringSoonArticles,
                'expired' => $expiredArticles,
            ],
            'qaMetrics' => [
                'total' => $totalDiscussions,
                'resolved' => $resolvedDiscussions,
                'unresolved' => $unresolvedDiscussions,
                'resolution_rate' => $resolutionRate,
            ],
            'spaceBreakdown' => $spaceBreakdown,
            'topAuthors' => $topAuthors,
            'topSkills' => $topSkills,
            'contentGaps' => $contentGaps,
            'urgentReviewArticles' => $urgentReviewArticles,
        ]);
    }
}
