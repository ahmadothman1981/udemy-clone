<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['verify', 'downloadSigned']),
        ];
    }

    /**
     * List user's certificates
     */
    public function index(Request $request)
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->with('course:id,title,slug,thumbnail')
            ->orderBy('issued_at', 'desc')
            ->get();

        return response()->json($certificates);
    }

    /**
     * Generate certificate for a completed course
     */
    public function generate(Request $request, Course $course)
    {
        $user = $request->user();

        // Check enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Not enrolled in this course'], 403);
        }

        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Certificate already issued',
                'certificate' => $existing,
            ]);
        }

        // Check course completion (100%)
        $course->load('sections.lectures');
        $totalLectures = $course->sections->sum(fn($s) => $s->lectures->count());

        if ($totalLectures === 0) {
            return response()->json(['message' => 'Course has no content'], 400);
        }

        $lectureIds = $course->sections->flatMap(fn($s) => $s->lectures->pluck('id'));
        $completedCount = UserProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lecture_id', $lectureIds)
            ->where('completed', true)
            ->count();

        $progress = round(($completedCount / $totalLectures) * 100);

        if ($progress < 100) {
            return response()->json([
                'message' => 'Course not completed',
                'progress' => $progress,
                'required' => 100,
            ], 400);
        }

        // Generate certificate
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => Certificate::generateCertificateNumber(),
            'issued_at' => now(),
        ]);

        $certificate->load('course:id,title,slug');

        return response()->json([
            'message' => 'Certificate generated successfully!',
            'certificate' => $certificate,
        ], 201);
    }

    /**
     * Download certificate as PDF
     */
    public function download(Request $request, Certificate $certificate)
    {
        $user = $request->user();

        // Check ownership
        if ($certificate->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $certificate->load(['user', 'course']);

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
        ]);

        $filename = 'certificate-' . $certificate->certificate_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get signed download URL
     */
    public function getDownloadUrl(Request $request, Certificate $certificate)
    {
        $user = $request->user();

        // Check ownership
        if ($certificate->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Generate temporary signed URL (valid for 5 minutes)
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'certificates.download.signed',
            now()->addMinutes(5),
            ['certificate' => $certificate->id, 'user' => $user->id]
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Download via signed URL (public route but validated)
     */
    public function downloadSigned(Request $request, Certificate $certificate, $user)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        // Extra check: ensure the certificate belongs to the user in the URL
        if ($certificate->user_id != $user) {
            abort(403);
        }

        $certificate->load(['user', 'course']);

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
        ]);

        $filename = 'certificate-' . $certificate->certificate_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Public verification of certificate
     */
    public function verify($certificateNumber)
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with(['user:id,name', 'course:id,title,slug'])
            ->first();

        if (!$certificate) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'certificate' => [
                'certificate_number' => $certificate->certificate_number,
                'issued_at' => $certificate->issued_at,
                'holder_name' => $certificate->user->name,
                'course_title' => $certificate->course->title,
            ],
        ]);
    }
}
