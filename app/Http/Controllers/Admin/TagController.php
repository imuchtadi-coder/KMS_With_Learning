<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('courses')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Admin/Tags/Index', compact('tags'));
    }

    public function create()
    {
        return Inertia::render('Admin/Tags/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['tenant_id'] = auth()->user()->tenant_id;

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag)
    {
        if ($tag->tenant_id !== auth()->user()->tenant_id) abort(403);

        return Inertia::render('Admin/Tags/Edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->tenant_id !== auth()->user()->tenant_id) abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        if ($tag->tenant_id !== auth()->user()->tenant_id) abort(403);

        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted.');
    }
}
