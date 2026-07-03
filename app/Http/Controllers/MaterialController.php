<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Material;
use App\Traits\HasTenantStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    use HasTenantStorage;


    /**
     * Store a new material file
     */
    public function store(Request $request, Lesson $lesson)
    {
        $user = auth()->user();
        $course = $lesson->section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|extensions:pdf,pptx,ppt,docx,doc|max:52428800',
        ]);

        $tenantId = $course->tenant_id;
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            self::tenantPath('materials/' . $lesson->id, $tenantId),
            $filename
        );

        Material::create([
            'lesson_id' => $lesson->id,
            'tenant_id' => $tenantId,
            'title' => $validated['title'],
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Material uploaded successfully!');
    }

    /**
     * Download a material file
     */
    public function download(Material $material)
    {
        // Authorize - check if user is instructor or student enrolled in course
        $lesson = $material->lesson;
        $course = $lesson->section->course;
        $user = auth()->user();

        // Allow if instructor or admin
        if ($user->id === $course->created_by || $user->hasRole('tenant_admin')) {
            // Instructor/Admin can download
        } elseif ($user->enrollments()->where('course_id', $course->id)->exists()) {
            // Student enrolled in course can download
        } else {
            abort(403, 'Unauthorized to download this material');
        }

        $filename = $material->title . '.' . $material->file_type;

        return Storage::download($material->file_path, $filename);
    }

    /**
     * Preview a material file (inline response for instructor)
     */
    public function instructorPreview(Material $material)
    {
        // Authorize - same as download
        $lesson = $material->lesson;
        $course = $lesson->section->course;
        $user = auth()->user();

        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403, 'Unauthorized to preview this material');
        }

        $filename = $material->title . '.' . $material->file_type;

        return Storage::response($material->file_path, $filename, [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download a material file (instructor route)
     */
    public function instructorDownload(Material $material)
    {
        // Authorize
        $lesson = $material->lesson;
        $course = $lesson->section->course;
        $user = auth()->user();

        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403, 'Unauthorized to download this material');
        }

        $filename = $material->title . '.' . $material->file_type;

        return Storage::download($material->file_path, $filename);
    }

    /**
     * Delete a material
     */
    public function destroy(Material $material)
    {
        $lesson = $material->lesson;
        $user = auth()->user();
        $course = $lesson->section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        Storage::delete($material->file_path);
        $material->delete();

        return redirect()->back()->with('success', 'Material deleted successfully!');
    }

    /**
     * Get lesson data and materials for modal (API endpoint)
     */
    public function getLessonData(Lesson $lesson)
    {
        // Authorize
        $user = auth()->user();
        $course = $lesson->section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $lesson->id,
            'title' => $lesson->title,
            'description' => $lesson->description ?: $lesson->content,
        ]);
    }

    /**
     * Get lesson materials for modal (API endpoint)
     */
    public function getLessonMaterials(Lesson $lesson)
    {
        // Authorize
        $user = auth()->user();
        $course = $lesson->section->course;
        if (!($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            !($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $materials = $lesson->materials()->get()->map(function ($material) {
            return [
                'id' => $material->id,
                'title' => $material->title,
                'file_type' => $material->file_type,
                'file_size' => $material->file_size,
                'created_at' => $material->created_at->format('M d, Y'),
            ];
        });

        return response()->json($materials);
    }
}
