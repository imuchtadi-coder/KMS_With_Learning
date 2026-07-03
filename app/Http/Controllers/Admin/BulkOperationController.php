<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\EnrollmentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BulkOperationController extends Controller
{
    public function enrolledStudents(Request $request)
    {
        $course = Course::findOrFail($request->course_id);
        $userIds = Enrollment::where('course_id', $course->id)->pluck('user_id');
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'email']);

        return response()->json($users);
    }
    public function importUsersForm()
    {
        return Inertia::render('Admin/Bulk/ImportUsers');
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);
        $tenantId = auth()->user()->tenant_id;

        $imported = 0;
        $errors = [];
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            $record = array_combine($headers, $data);

            try {
                $validated = validator($record, [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'unique:users,email'],
                    'password' => ['required', 'string', 'min:6'],
                    'role' => ['required', 'in:student,instructor'],
                    'department' => ['nullable', 'string', 'max:255'],
                ])->validate();

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'tenant_id' => $tenantId,
                    'department' => $validated['department'] ?? null,
                ]);

                $user->assignRole($validated['role']);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$row}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        return redirect()->route('admin.bulk.import-users')
            ->with('success', "Imported {$imported} user(s).")
            ->with('errors', $errors);
    }

    public function bulkEnrollForm()
    {
        $courses = Course::where('tenant_id', auth()->user()->tenant_id)
            ->where('published', true)
            ->orderBy('title')
            ->get();

        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->role('student')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Bulk/Enroll', compact('courses', 'users'));
    }

    public function bulkEnroll(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $enrolled = 0;

        foreach ($validated['user_ids'] as $userId) {
            $exists = Enrollment::where('user_id', $userId)
                ->where('course_id', $course->id)
                ->exists();

            if (!$exists) {
                $enrollment = Enrollment::create([
                    'user_id' => $userId,
                    'course_id' => $course->id,
                ]);

                $user = User::find($userId);
                $user->notify(new EnrollmentConfirmation($course));
                $enrolled++;
            }
        }

        return redirect()->route('admin.bulk.enroll')
            ->with('success', "Enrolled {$enrolled} student(s) in {$course->title}.");
    }

    public function bulkCertificatesForm()
    {
        $courses = Course::where('tenant_id', auth()->user()->tenant_id)
            ->where('published', true)
            ->orderBy('title')
            ->get();

        return Inertia::render('Admin/Bulk/Certificates', compact('courses'));
    }

    public function bulkCertificates(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $generated = 0;
        $errors = [];

        foreach ($validated['user_ids'] as $userId) {
            try {
                $cert = Certificate::firstOrCreate(
                    ['user_id' => $userId, 'course_id' => $course->id],
                    [
                        'certificate_number' => Certificate::generateCertificateNumber(),
                        'issued_at' => now(),
                    ]
                );

                if (!$cert->pdf_path) {
                    \App\Http\Controllers\CertificateController::generatePDF($cert);
                }

                $generated++;
            } catch (\Exception $e) {
                $errors[] = "User {$userId}: {$e->getMessage()}";
            }
        }

        return redirect()->route('admin.bulk.certificates')
            ->with('success', "Generated {$generated} certificate(s).")
            ->with('errors', $errors);
    }
}
