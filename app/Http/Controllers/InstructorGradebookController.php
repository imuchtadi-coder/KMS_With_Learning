<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradebookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InstructorGradebookController extends Controller
{
    public function index(Course $course)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $gradebookItems = GradebookItem::where('course_id', $course->id)->get();
        $enrollments = Enrollment::where('course_id', $course->id)
            ->with('user')
            ->get();

        $students = $enrollments->map(function ($enrollment) use ($gradebookItems) {
            $grades = Grade::where('user_id', $enrollment->user_id)
                ->whereIn('gradebook_item_id', $gradebookItems->pluck('id'))
                ->get()
                ->keyBy('gradebook_item_id');

            $totalPercentage = 0;
            $totalWeight = 0;
            $itemGrades = [];

            foreach ($gradebookItems as $item) {
                $grade = $grades->get($item->id);
                $itemPercentage = $grade ? $grade->percentage : null;
                $itemGrades[$item->id] = [
                    'score' => $grade ? $grade->score : null,
                    'percentage' => $itemPercentage,
                    'letter_grade' => $grade ? $grade->letter_grade : '-',
                ];
                if ($itemPercentage !== null) {
                    $totalPercentage += $itemPercentage * $item->weight;
                    $totalWeight += $item->weight;
                }
            }

            $finalPercentage = $totalWeight > 0 ? round($totalPercentage / $totalWeight, 2) : null;
            $enrollment->item_grades = $itemGrades;
            $enrollment->final_percentage = $finalPercentage;
            $enrollment->final_grade = $finalPercentage ? $this->calculateLetterGrade($finalPercentage) : '-';
            $enrollment->total_weight = $totalWeight;

            return $enrollment;
        });

        return Inertia::render('Instructor/Gradebook/Index', compact('course', 'gradebookItems', 'students'));
    }

    public function exportCsv(Course $course)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $gradebookItems = GradebookItem::where('course_id', $course->id)->get();
        $enrollments = Enrollment::where('course_id', $course->id)->with('user')->get();

        $callback = function () use ($gradebookItems, $enrollments) {
            $handle = fopen('php://output', 'w');

            // Header row
            $headers = ['Student', 'Email'];
            foreach ($gradebookItems as $item) {
                $headers[] = $item->title.' (Score)';
                $headers[] = $item->title.' (%)';
                $headers[] = $item->title.' (Grade)';
            }
            $headers[] = 'Final (%)';
            $headers[] = 'Final Grade';
            fputcsv($handle, $headers);

            // Data rows
            foreach ($enrollments as $enrollment) {
                $grades = Grade::where('user_id', $enrollment->user_id)
                    ->whereIn('gradebook_item_id', $gradebookItems->pluck('id'))
                    ->get()
                    ->keyBy('gradebook_item_id');

                $row = [$enrollment->user->name, $enrollment->user->email];
                $totalPercentage = 0;
                $totalWeight = 0;

                foreach ($gradebookItems as $item) {
                    $grade = $grades->get($item->id);
                    $row[] = $grade ? $grade->score : '';
                    $row[] = $grade ? $grade->percentage : '';
                    $row[] = $grade ? $grade->letter_grade : '';
                    if ($grade && $grade->percentage !== null) {
                        $totalPercentage += $grade->percentage * $item->weight;
                        $totalWeight += $item->weight;
                    }
                }

                $finalPct = $totalWeight > 0 ? round($totalPercentage / $totalWeight, 2) : '';
                $row[] = $finalPct;
                $row[] = $finalPct !== '' ? $this->calculateLetterGrade((float) $finalPct) : '';
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        $filename = 'gradebook-'.Str::slug($course->title).'-'.now()->format('Y-m-d').'.csv';

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function updateGrade(Request $request, Course $course)
    {
        $user = auth()->user();
        if (! ($user->id === $course->created_by && $user->tenant_id === $course->tenant_id) &&
            ! ($user->hasRole('tenant_admin') && $user->tenant_id === $course->tenant_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('enrollments', 'user_id')->where('course_id', $course->id),
                Rule::exists('users', 'id')->where('tenant_id', $course->tenant_id),
            ],
            'gradebook_item_id' => [
                'required',
                Rule::exists('gradebook_items', 'id')->where('course_id', $course->id),
            ],
            'score' => 'nullable|numeric|min:0',
        ]);

        $item = GradebookItem::findOrFail($validated['gradebook_item_id']);
        $score = $validated['score'];
        $percentage = $score !== null ? round(($score / $item->max_score) * 100, 2) : null;
        $letterGrade = $percentage !== null ? $this->calculateLetterGrade($percentage) : null;

        $grade = Grade::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'gradebook_item_id' => $validated['gradebook_item_id'],
            ],
            [
                'course_id' => $course->id,
                'score' => $score,
                'percentage' => $percentage,
                'letter_grade' => $letterGrade,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'grade' => $grade]);
        }

        return redirect()->back()->with('success', 'Grade updated successfully.');
    }

    private function calculateLetterGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'E',
        };
    }
}
