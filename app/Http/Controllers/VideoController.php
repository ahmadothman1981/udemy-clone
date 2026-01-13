<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function stream(Request $request, Lecture $lecture, $filename)
    {
        // 1. Authorization
        $user = Auth::user();
        $course = $lecture->section->course;

        // Check if user is instructor
        $isInstructor = $user->id === $course->instructor_id;

        // Check if user is enrolled
        $isEnrolled = $course->enrollments()->where('user_id', $user->id)->exists();

        // Check if admin (optional)
        $isAdmin = $user->hasRole('admin') || $user->hasRole('superadmin');

        if (!$isInstructor && !$isEnrolled && !$isAdmin) {
            abort(403, 'Unauthorized to view this content.');
        }

        // 2. Validate Path
        // The path in database might be relative or absolute, but here we expect
        // the request to be for a specific file inside the lecture folder.
        // E.g. /stream/{lecture_id}/playlist.m3u8
        // E.g. /stream/{lecture_id}/segment_001.ts

        // Construct the expected path on the 'lectures' disk
        $path = 'lectures/' . $course->id . '/' . $lecture->id . '/' . $filename;

        // 3. Serve File
        if (!Storage::disk('lectures')->exists($path)) {
            abort(404);
        }

        return Storage::disk('lectures')->download($path, null, [
            'Content-Type' => $this->getMimeType($filename),
            'Cache-Control' => 'no-cache, private', // Ensure authentication is checked
            'Access-Control-Allow-Origin' => '*', // CORS if needed for player
        ]);
    }

    private function getMimeType($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return match ($ext) {
            'm3u8' => 'application/vnd.apple.mpegurl',
            'ts' => 'video/MP2T',
            'mp4' => 'video/mp4',
            default => 'application/octet-stream',
        };
    }
}
