<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\KnowledgeSpace;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KnowledgeSpaceController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $user = $request->user();

        $query = KnowledgeSpace::with(['creator', 'manager'])
            ->withCount([
                'articles as total_articles_count',
                'articles as published_articles_count' => fn($q) => $q->where('status', 'published'),
            ]);

        if (!$user->hasRole('superadmin')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        $spaces = $query->latest()->get();

        $canManage = $user->hasRole(['superadmin', 'tenant_admin', 'instructor']);

        $managers = [];
        if ($canManage) {
            $managers = User::query()
                ->when(!$user->hasRole('superadmin'), fn($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return Inertia::render('Spaces/Index', [
            'spaces' => $spaces,
            'canManage' => $canManage,
            'managers' => $managers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Request $request, KnowledgeSpace $space)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        if (!$user->hasRole('superadmin') && $space->tenant_id !== $tenantId) {
            abort(403, 'Akses ke Knowledge Space ini tidak diizinkan.');
        }

        $space->load(['creator', 'manager']);

        // Load root articles with nested children for tree navigation (Notion/Confluence style)
        $articleTree = Article::where('space_id', $space->id)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->with('children')->orderBy('order');
            }, 'author'])
            ->when(!$user->hasRole(['superadmin', 'tenant_admin', 'instructor']), function ($q) use ($user) {
                $q->where(fn($sub) => $sub->where('status', 'published')->orWhere('author_id', $user->id));
            })
            ->orderBy('order')
            ->get();

        $stats = [
            'total' => Article::where('space_id', $space->id)->count(),
            'published' => Article::where('space_id', $space->id)->where('status', 'published')->count(),
            'needs_review' => Article::where('space_id', $space->id)->needsReview()->count(),
        ];

        return Inertia::render('Spaces/Show', [
            'space' => $space,
            'articleTree' => $articleTree,
            'stats' => $stats,
            'canManage' => $user->hasRole(['superadmin', 'tenant_admin', 'instructor']),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) {
            abort(403, 'Anda tidak memiliki hak akses membuat Knowledge Space.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'visibility' => 'required|in:public,internal,restricted',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $space = KnowledgeSpace::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: 'mdi-folder-outline',
            'color' => $validated['color'] ?: '#4F46E5',
            'visibility' => $validated['visibility'],
            'created_by' => $user->id,
            'manager_id' => $validated['manager_id'] ?? $user->id,
        ]);

        return redirect()->route('spaces.show', $space->id)->with('success', 'Knowledge Space baru berhasil dibuat!');
    }

    public function update(Request $request, KnowledgeSpace $space)
    {
        $user = $request->user();
        if (!$user->hasRole(['superadmin', 'tenant_admin', 'instructor'])) {
            abort(403, 'Anda tidak memiliki hak akses mengubah Knowledge Space ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'visibility' => 'required|in:public,internal,restricted',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $space->update($validated);

        return redirect()->back()->with('success', 'Informasi Knowledge Space berhasil diperbarui.');
    }

    public function destroy(Request $request, KnowledgeSpace $space)
    {
        $user = $request->user();
        if (!$user->hasRole(['superadmin', 'tenant_admin'])) {
            abort(403, 'Hanya Administrator yang dapat menghapus Space.');
        }

        $space->delete();

        return redirect()->route('spaces.index')->with('success', 'Knowledge Space berhasil diarsipkan.');
    }
}
