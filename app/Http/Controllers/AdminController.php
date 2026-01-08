<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            // check for admin role in real app, e.g. 'can:admin_access'
        ];
    }

    // Platform Stats
    public function stats()
    {
        // Simple aggregation
        return response()->json([
            'total_users' => User::count(),
            'total_instructors' => User::has('courses_taught')->count(),
            'total_courses' => Course::count(),
            'total_revenue' => Order::sum('total_amount'), // Assuming Order model has total_amount
            'pending_courses' => Course::where('status', 'pending')->count(),
        ]);
    }

    // User Management
    public function users(Request $request)
    {
        return response()->json(User::latest()->paginate(20));
    }

    // Pending Courses
    public function pendingCourses()
    {
        $courses = Course::where('status', 'pending')
            ->with('instructor', 'category')
            ->get();
        return response()->json($courses);
    }

    // Approve/Reject Course
    public function approveCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|string'
        ]);

        if ($validated['action'] === 'approve') {
            $course->update(['status' => 'published', 'published' => true]);
        } else {
            $course->update(['status' => 'rejected']);
            // Send email to instructor with $validated['reason']
        }

        return response()->json(['message' => 'Course updated']);
    }
}
