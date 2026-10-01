<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Progress;
use App\Traits\HasTenantStorage;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    use HasTenantStorage;

    /**
     * Generate certificate for student if course is completed
     */
    public static function generateIfEligible($userId, $courseId)
    {
        // Check if certificate already exists
        if (Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists()) {
            return null;
        }

        // Check if all lessons are completed
        $course = Course::with(['sections.lessons'])->find($courseId);
        $lessons = $course->sections->flatMap->lessons;

        $completedLessons = Progress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->where('completed', true)
            ->count();

        // Only generate if all lessons completed
        if ($completedLessons !== $lessons->count()) {
            return null;
        }

        // Generate certificate
        $certificateNumber = 'CERT-' . strtoupper(substr(md5($userId . $courseId . now()), 0, 8));

        $certificate = Certificate::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'certificate_number' => $certificateNumber,
            'issued_at' => now(),
        ]);

        // Generate PDF
        self::generatePDF($certificate);

        return $certificate;
    }

    /**
     * Generate PDF for certificate
     */
    public static function generatePDF(Certificate $certificate)
    {
        $user = $certificate->user;
        $course = $certificate->course;

        $course->loadMissing(['instructor', 'tenant.branding',]);

        $tenant = $course->tenant;
        $branding = $tenant?->branding;

        $certificateLogo = null;
        $certificateLogoWidth = null;
        $certificateLogoHeight = null;

        if (
            $branding?->certificate_logo_path &&
            Storage::disk('public')->exists($branding->certificate_logo_path)
        ) {
            $certificateLogo = Storage::disk('public')
                ->path($branding->certificate_logo_path);
        } elseif (
            $branding?->logo_path &&
            Storage::disk('public')->exists($branding->logo_path)
        ) {
            $certificateLogo = Storage::disk('public')
                ->path($branding->logo_path);
        }

        /*
        |--------------------------------------------------------------------------
        | ADAPTIVE LOGO SIZE
        |--------------------------------------------------------------------------
        */

        if ($certificateLogo && is_file($certificateLogo)) {
            $imageSize = @getimagesize($certificateLogo);

            if ($imageSize && $imageSize[0] > 0 && $imageSize[1] > 0) {

                $imageWidth = $imageSize[0];
                $imageHeight = $imageSize[1];


                $maxWidth = 55;
                $maxHeight = 22;

                $scale = min(
                    $maxWidth / $imageWidth,
                    $maxHeight / $imageHeight
                );

                $certificateLogoWidth = round(
                    $imageWidth * $scale,
                    2
                );

                $certificateLogoHeight = round(
                    $imageHeight * $scale,
                    2
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RENDER CERTIFICATE
        |--------------------------------------------------------------------------
        */

        $html = view('certificates.template', [
            'user' => $user,
            'course' => $course,
            'certificate' => $certificate,
            'tenant' => $tenant,
            'branding' => $branding,

            'certificate_logo' => $certificateLogo,
            'certificate_logo_width' => $certificateLogoWidth,
            'certificate_logo_height' => $certificateLogoHeight,

            'issued_date' => $certificate->issued_at->format('F d, Y'),
        ])->render();

        /*
        |--------------------------------------------------------------------------
        | GENERATE PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        /*
        |--------------------------------------------------------------------------
        | SAVE PDF
        |--------------------------------------------------------------------------
        */

        $filename = self::tenantPath(
            "certificates/{$certificate->user_id}/{$certificate->course_id}.pdf",
            $certificate->course->tenant_id
        );

        Storage::put($filename, $pdf->output());

        $certificate->update([
            'pdf_path' => $filename,
        ]);
    }

    /**
     * Download certificate PDF
     */
    public function download(Certificate $certificate)
    {
        // Authorize
        $user = auth()->user();
        if ($user->id !== $certificate->user_id && !$user->hasRole('tenant_admin')) {
            abort(403, 'Unauthorized');
        }

        $filename = "{$certificate->course->title} - Certificate.pdf";

        return Storage::download($certificate->pdf_path, $filename);
    }

    /**
     * View certificate
     */
    public function view(Certificate $certificate)
    {
        // Authorize
        $user = auth()->user();
        if ($user->id !== $certificate->user_id && !$user->hasRole('tenant_admin')) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::exists($certificate->pdf_path)) {
            self::generatePDF($certificate);
        }

        $pdf = Storage::get($certificate->pdf_path);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="certificate.pdf"');
    }

    /**
     * List certificates for authenticated user
     */
    public function list()
    {
        $certificates = auth()->user()
            ->certificates()
            ->with('course')
            ->paginate(10);

        return response()->json($certificates);
    }
}
