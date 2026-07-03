<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BadgeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $badges = Badge::where('tenant_id', $user->tenant_id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Badges/Index', compact('badges'));
    }

    public function create()
    {
        return Inertia::render('Admin/Badges/Create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'criteria_type' => ['required', 'in:lesson_complete,course_complete,quiz_perfect_score,points_milestone,consecutive_days'],
            'criteria_value' => ['required', 'integer', 'min:1'],
            'active' => ['boolean'],
        ]);

        Badge::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . uniqid(),
            'description' => $validated['description'],
            'image_url' => $validated['image_url'],
            'criteria_type' => $validated['criteria_type'],
            'criteria_value' => $validated['criteria_value'],
            'active' => $validated['active'] ?? true,
        ]);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge created successfully!');
    }

    public function edit(Badge $badge)
    {
        $user = auth()->user();
        if ($badge->tenant_id !== $user->tenant_id) abort(403);

        return Inertia::render('Admin/Badges/Edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge)
    {
        $user = auth()->user();
        if ($badge->tenant_id !== $user->tenant_id) abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'criteria_type' => ['required', 'in:lesson_complete,course_complete,quiz_perfect_score,points_milestone,consecutive_days'],
            'criteria_value' => ['required', 'integer', 'min:1'],
            'active' => ['boolean'],
        ]);

        $badge->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . $badge->id,
            'description' => $validated['description'],
            'image_url' => $validated['image_url'],
            'criteria_type' => $validated['criteria_type'],
            'criteria_value' => $validated['criteria_value'],
            'active' => $validated['active'] ?? true,
        ]);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge updated successfully!');
    }

    public function destroy(Badge $badge)
    {
        $user = auth()->user();
        if ($badge->tenant_id !== $user->tenant_id) abort(403);

        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge deleted successfully!');
    }
}
