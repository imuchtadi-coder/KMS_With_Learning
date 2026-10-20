<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleAttachment;
use App\Models\ArticleReview;
use App\Models\ArticleVersion;
use App\Models\Category;
use App\Models\KnowledgeSpace;
use App\Models\Tag;
use App\Services\DocumentTextExtractor;
use App\Traits\HasTenantStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    use HasTenantStorage;

    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        $query = Article::with(['author', 'category', 'tags', 'reviewer', 'space', 'parent'])
            ->withCount(['attachments', 'versions', 'children']);

        if (!$user->hasRole('superadmin')) {
            $query->where('tenant_id', $tenantId);
        }

        $isSme = $user->hasRole(['superadmin', 'tenant_admin', 'instructor']);

        // Visibility: non-admins only see published articles, their own articles, or articles pending review if they are SME
        if (!$user->hasRole(['superadmin', 'tenant_admin'])) {
            $query->where(function ($q) use ($user, $isSme) {
                $q->where('status', 'published')
                  ->orWhere('author_id', $user->id);
                if ($isSme) {
                    $q->orWhere('status', 'pending_review');
                }
            });
        }

        // Space filter (Confluence-style Spaces)
        if ($request->filled('space_id')) {
            $query->where('space_id', $request->space_id);
        }

        // Search filter with PostgreSQL Full-Text Search & rank
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("search_vector @@ websearch_to_tsquery('simple', ?)", [$search])
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(excerpt) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(content) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->selectRaw("articles.*, ts_rank_cd(search_vector, websearch_to_tsquery('simple', ?)) as fts_rank", [$search])
            ->orderByDesc('fts_rank');
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Tag filter
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Needs Review / Document Lifecycle filter
        if ($request->boolean('needs_review')) {
            $query->needsReview();
        }

        $articles = $query->latest('created_at')->paginate(12)->withQueryString();

        $spaces = KnowledgeSpace::query();
        if (!$user->hasRole('superadmin')) {
            $spaces->where('tenant_id', $tenantId);
        }
        $spaces = $spaces->active()->withCount('articles')->orderBy('name')->get();

        $categories = Category::query();
        if (!$user->hasRole('superadmin')) {
            $categories->where('tenant_id', $tenantId);
        }
        $categories = $categories->withCount('articles')->orderBy('name')->get();

        $tags = Tag::query();
        if (!$user->hasRole('superadmin')) {
            $tags->where('tenant_id', $tenantId);
        }
        $tags = $tags->withCount('articles')->orderBy('name')->get();

        // Pending review count for SME review queue
        $pendingReviewCount = 0;
        if ($isSme) {
            $pendingQuery = Article::where('status', 'pending_review');
            if (!$user->hasRole('superadmin')) {
                $pendingQuery->where('tenant_id', $tenantId);
            }
            $pendingReviewCount = $pendingQuery->count();
        }

        // Needs review (expiry) count
        $needsReviewQuery = Article::needsReview();
        if (!$user->hasRole('superadmin')) {
            $needsReviewQuery->where('tenant_id', $tenantId);
        }
        $needsReviewCount = $needsReviewQuery->count();

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'spaces' => $spaces,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => $request->only(['search', 'space_id', 'category_id', 'tag_id', 'status', 'needs_review']),
            'canCreate' => $user->can('create', Article::class),
            'isSme' => $isSme,
            'pendingReviewCount' => $pendingReviewCount,
            'needsReviewCount' => $needsReviewCount,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Article::class);

        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        $spaces = KnowledgeSpace::query();
        if (!$user->hasRole('superadmin')) {
            $spaces->where('tenant_id', $tenantId);
        }
        $spaces = $spaces->active()->orderBy('name')->get();

        $categories = Category::query();
        if (!$user->hasRole('superadmin')) {
            $categories->where('tenant_id', $tenantId);
        }
        $categories = $categories->orderBy('name')->get();

        $tags = Tag::query();
        if (!$user->hasRole('superadmin')) {
            $tags->where('tenant_id', $tenantId);
        }
        $tags = $tags->orderBy('name')->get();

        $potentialParents = Article::query();
        if (!$user->hasRole('superadmin')) {
            $potentialParents->where('tenant_id', $tenantId);
        }
        $potentialParents = $potentialParents->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'space_id']);

        $canPublishDirectly = $user->hasRole(['superadmin', 'tenant_admin', 'instructor']);

        return Inertia::render('Articles/Create', [
            'spaces' => $spaces,
            'categories' => $categories,
            'tags' => $tags,
            'potentialParents' => $potentialParents,
            'canPublishDirectly' => $canPublishDirectly,
            'defaultSpaceId' => $request->query('space_id') ? (int) $request->query('space_id') : null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Article::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'space_id' => 'nullable|exists:knowledge_spaces,id',
            'parent_id' => 'nullable|exists:articles,id',
            'order' => 'nullable|integer',
            'review_cycle_days' => 'nullable|integer|min:15|max:730',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'status' => 'required|in:draft,pending_review,published',
            'version' => 'nullable|string|max:20',
            'change_summary' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:51200',
        ]);

        $user = $request->user();
        $tenantId = $user->tenant_id;

        // If user is not SME/admin and selects published, automatically redirect to pending_review
        $status = $validated['status'];
        if ($status === 'published' && !$user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) {
            $status = 'pending_review';
        }

        $versionNumber = $validated['version'] ?: '1.0';
        $cycleDays = $validated['review_cycle_days'] ?? 180;

        $article = Article::create([
            'tenant_id' => $tenantId,
            'space_id' => $validated['space_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'order' => $validated['order'] ?? 0,
            'review_cycle_days' => $cycleDays,
            'title' => $validated['title'],
            'version' => $versionNumber,
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 160),
            'content' => $validated['content'],
            'category_id' => $validated['category_id'] ?? null,
            'author_id' => $user->id,
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
            'next_review_at' => $status === 'published' ? now()->addDays($cycleDays) : null,
            'reviewed_by' => $status === 'published' ? $user->id : null,
            'reviewed_at' => $status === 'published' ? now() : null,
        ]);

        // Save initial version snapshot
        ArticleVersion::create([
            'article_id' => $article->id,
            'version' => $versionNumber,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'change_summary' => $validated['change_summary'] ?: 'Versi awal dokumen (Inisialisasi)',
            'created_by' => $user->id,
            'reviewed_by' => $status === 'published' ? $user->id : null,
            'reviewed_at' => $status === 'published' ? now() : null,
        ]);

        if (!empty($validated['tag_ids'])) {
            $article->tags()->sync($validated['tag_ids']);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $ext;
                $path = $file->storeAs(
                    self::tenantPath('articles/' . $article->id, $tenantId),
                    $filename
                );

                $extractedText = DocumentTextExtractor::extractText($path, $ext);

                ArticleAttachment::create([
                    'article_id' => $article->id,
                    'tenant_id' => $tenantId,
                    'title' => $originalName,
                    'file_path' => $path,
                    'file_type' => $ext,
                    'file_size' => $file->getSize(),
                    'extracted_text' => $extractedText,
                ]);
            }
        }

        $msg = $status === 'pending_review'
            ? 'Artikel berhasil disimpan dan diajukan untuk review oleh Subject Matter Expert (SME).'
            : 'Artikel pengetahuan berhasil dibuat!';

        return redirect()->route('articles.show', $article->id)->with('success', $msg);
    }

    public function show(Request $request, Article $article)
    {
        $this->authorize('view', $article);

        if ($article->status === 'published') {
            $article->increment('views_count');
        }

        $article->load([
            'author',
            'category',
            'tags',
            'attachments',
            'reviewer',
            'space.manager',
            'parent',
            'children.author',
            'reviewLogs.reviewer',
            'versions.creator',
            'versions.reviewer',
        ]);

        $user = $request->user();
        $canEdit = $user->can('update', $article);
        $canDelete = $user->can('delete', $article);
        $canReview = $user->can('review', $article);
        $canSubmitReview = $user->can('submitReview', $article);

        // Check if viewing historical version snapshot
        $viewingVersion = null;
        if ($request->filled('version_id')) {
            $viewingVersion = $article->versions->firstWhere('id', (int) $request->version_id);
        }

        // Confluence-like Space document tree
        $spaceTree = [];
        if ($article->space_id) {
            $spaceTree = Article::where('space_id', $article->space_id)
                ->whereNull('parent_id')
                ->with(['children' => fn($q) => $q->orderBy('order')])
                ->where(function ($q) use ($user) {
                    $q->where('status', 'published')
                      ->orWhere('author_id', $user->id);
                })
                ->orderBy('order')
                ->get(['id', 'title', 'slug', 'parent_id', 'space_id', 'version', 'status']);
        }

        // Related articles from same category or space
        $relatedArticles = Article::query()
            ->where('tenant_id', $article->tenant_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'published')
            ->when($article->space_id, fn($q) => $q->where('space_id', $article->space_id))
            ->when(!$article->space_id && $article->category_id, fn($q) => $q->where('category_id', $article->category_id))
            ->latest('published_at')
            ->limit(4)
            ->get(['id', 'title', 'slug', 'excerpt', 'created_at', 'views_count']);

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'spaceTree' => $spaceTree,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
            'canReview' => $canReview,
            'canSubmitReview' => $canSubmitReview,
            'viewingVersion' => $viewingVersion,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    public function edit(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $article->load(['tags', 'attachments', 'versions']);

        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        $spaces = KnowledgeSpace::query();
        if (!$user->hasRole('superadmin')) {
            $spaces->where('tenant_id', $tenantId);
        }
        $spaces = $spaces->active()->orderBy('name')->get();

        $categories = Category::query();
        if (!$user->hasRole('superadmin')) {
            $categories->where('tenant_id', $tenantId);
        }
        $categories = $categories->orderBy('name')->get();

        $tags = Tag::query();
        if (!$user->hasRole('superadmin')) {
            $tags->where('tenant_id', $tenantId);
        }
        $tags = $tags->orderBy('name')->get();

        $potentialParents = Article::query();
        if (!$user->hasRole('superadmin')) {
            $potentialParents->where('tenant_id', $tenantId);
        }
        $potentialParents = $potentialParents->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->orderBy('title')
            ->get(['id', 'title', 'space_id']);

        $canPublishDirectly = $user->hasRole(['superadmin', 'tenant_admin', 'instructor']);

        return Inertia::render('Articles/Edit', [
            'article' => $article,
            'spaces' => $spaces,
            'categories' => $categories,
            'tags' => $tags,
            'potentialParents' => $potentialParents,
            'canPublishDirectly' => $canPublishDirectly,
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'space_id' => 'nullable|exists:knowledge_spaces,id',
            'parent_id' => 'nullable|exists:articles,id',
            'order' => 'nullable|integer',
            'review_cycle_days' => 'nullable|integer|min:15|max:730',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'status' => 'required|in:draft,pending_review,published,rejected,archived',
            'change_summary' => 'nullable|string|max:255',
            'new_version' => 'nullable|string|max:20',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:51200',
        ]);

        $user = $request->user();

        // Compute next version string (e.g. 1.0 -> 1.1) if not explicitly set
        $currentVersion = $article->version ?: '1.0';
        $nextVersion = $validated['new_version'];
        if (empty($nextVersion)) {
            $parts = explode('.', $currentVersion);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $nextVersion = $parts[0] . '.' . ((int)$parts[1] + 1);
            } else {
                $nextVersion = $currentVersion . '.1';
            }
        }

        $status = $validated['status'];
        if ($status === 'published' && !$user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) {
            $status = 'pending_review';
        }

        $publishedAt = $article->published_at;
        $cycleDays = $validated['review_cycle_days'] ?? ($article->review_cycle_days ?: 180);
        $nextReviewAt = $article->next_review_at;

        if ($status === 'published') {
            if (!$publishedAt) {
                $publishedAt = now();
            }
            if (!$nextReviewAt) {
                $nextReviewAt = now()->addDays($cycleDays);
            }
        }

        $article->update([
            'space_id' => $validated['space_id'] ?? $article->space_id,
            'parent_id' => $validated['parent_id'] ?? $article->parent_id,
            'order' => $validated['order'] ?? $article->order,
            'review_cycle_days' => $cycleDays,
            'next_review_at' => $nextReviewAt,
            'title' => $validated['title'],
            'version' => $nextVersion,
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 160),
            'content' => $validated['content'],
            'category_id' => $validated['category_id'] ?? null,
            'status' => $status,
            'published_at' => $publishedAt,
            'review_notes' => ($status === 'pending_review') ? null : $article->review_notes,
            'reviewed_by' => ($status === 'published' && $user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) ? $user->id : $article->reviewed_by,
            'reviewed_at' => ($status === 'published' && $user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) ? now() : $article->reviewed_at,
        ]);

        // Create new version snapshot in history
        ArticleVersion::create([
            'article_id' => $article->id,
            'version' => $nextVersion,
            'title' => $validated['title'],
            'excerpt' => $article->excerpt,
            'content' => $validated['content'],
            'change_summary' => $validated['change_summary'] ?: "Revisi versi {$nextVersion}",
            'created_by' => $user->id,
            'reviewed_by' => ($status === 'published') ? $user->id : null,
            'reviewed_at' => ($status === 'published') ? now() : null,
        ]);

        $article->tags()->sync($validated['tag_ids'] ?? []);

        if ($request->hasFile('attachments')) {
            $tenantId = $article->tenant_id;
            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $ext;
                $path = $file->storeAs(
                    self::tenantPath('articles/' . $article->id, $tenantId),
                    $filename
                );

                $extractedText = DocumentTextExtractor::extractText($path, $ext);

                ArticleAttachment::create([
                    'article_id' => $article->id,
                    'tenant_id' => $tenantId,
                    'title' => $originalName,
                    'file_path' => $path,
                    'file_type' => $ext,
                    'file_size' => $file->getSize(),
                    'extracted_text' => $extractedText,
                ]);
            }
        }

        return redirect()->route('articles.show', $article->id)
            ->with('success', "Artikel berhasil diperbarui ke Versi {$nextVersion}!");
    }

    public function submitReview(Request $request, Article $article)
    {
        $this->authorize('submitReview', $article);

        $article->update([
            'status' => 'pending_review',
            'review_notes' => null,
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil diajukan untuk ditinjau oleh Subject Matter Expert (SME).');
    }

    public function approve(Request $request, Article $article)
    {
        $this->authorize('review', $article);

        $user = $request->user();
        $notes = $request->input('review_notes') ?: 'Disetujui oleh SME dan resmi dipublikasikan.';
        $cycle = $article->review_cycle_days ?: 180;

        $article->update([
            'status' => 'published',
            'published_at' => $article->published_at ?? now(),
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
            'next_review_at' => now()->addDays($cycle),
        ]);

        // Record in ArticleReview audit log
        ArticleReview::create([
            'article_id' => $article->id,
            'version' => $article->version ?: '1.0',
            'reviewer_id' => $user->id,
            'status' => 'approved',
            'notes' => $notes,
        ]);

        // Update latest version snapshot approval details
        $latestVersion = $article->versions()->latest('id')->first();
        if ($latestVersion) {
            $latestVersion->update([
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil disetujui (Approved) dan resmi dipublikasikan!');
    }

    public function reject(Request $request, Article $article)
    {
        $this->authorize('review', $article);

        $validated = $request->validate([
            'review_notes' => 'required|string|min:5|max:1000',
        ]);

        $user = $request->user();

        $article->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        // Record in ArticleReview audit log
        ArticleReview::create([
            'article_id' => $article->id,
            'version' => $article->version ?: '1.0',
            'reviewer_id' => $user->id,
            'status' => 'revision_requested',
            'notes' => $validated['review_notes'],
        ]);

        return redirect()->back()->with('success', 'Permintaan revisi berhasil dikirimkan kepada penulis.');
    }

    public function restoreVersion(Request $request, Article $article, ArticleVersion $version)
    {
        $this->authorize('update', $article);
        abort_unless($version->article_id === $article->id, 404);

        $user = $request->user();

        // Increment version for the restoration
        $currentParts = explode('.', $article->version ?: '1.0');
        $newVersion = ($currentParts[0] ?? '1') . '.' . (((int)($currentParts[1] ?? '0')) + 1);

        $article->update([
            'title' => $version->title,
            'excerpt' => $version->excerpt,
            'content' => $version->content,
            'version' => $newVersion,
        ]);

        ArticleVersion::create([
            'article_id' => $article->id,
            'version' => $newVersion,
            'title' => $version->title,
            'excerpt' => $version->excerpt,
            'content' => $version->content,
            'change_summary' => "Pemulihan (Rollback) dari versi {$version->version}",
            'created_by' => $user->id,
            'reviewed_by' => ($article->status === 'published') ? $user->id : null,
            'reviewed_at' => ($article->status === 'published') ? now() : null,
        ]);

        return redirect()->route('articles.show', $article->id)
            ->with('success', "Dokumen berhasil dipulihkan dari Versi {$version->version} menjadi Versi {$newVersion}!");
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return redirect()->route('articles.index')
            ->with('success', 'Artikel berhasil dihapus!');
    }

    public function downloadAttachment(ArticleAttachment $attachment)
    {
        $article = $attachment->article;
        $user = auth()->user();

        if (!$user->hasRole('superadmin') && $user->tenant_id !== $attachment->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $attachment->increment('downloads_count');

        $filename = $attachment->title . '.' . $attachment->file_type;

        return Storage::download($attachment->file_path, $filename);
    }

    public function deleteAttachment(ArticleAttachment $attachment)
    {
        $article = $attachment->article;
        $this->authorize('update', $article);

        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
