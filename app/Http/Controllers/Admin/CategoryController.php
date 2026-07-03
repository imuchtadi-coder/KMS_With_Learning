<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('tenant_id', auth()->user()->tenant_id)
            ->with('parent')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Admin/Categories/Index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['tenant_id'] = auth()->user()->tenant_id;

        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        if ($category->tenant_id !== auth()->user()->tenant_id) abort(403);

        $parents = Category::where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->tenant_id !== auth()->user()->tenant_id) abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id', 'not_in:' . $category->id],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->tenant_id !== auth()->user()->tenant_id) abort(403);

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }
}
