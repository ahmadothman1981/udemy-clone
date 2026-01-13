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
        // System Health Check
        $dbStatus = true;
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = false;
        }

        $cacheStatus = \Illuminate\Support\Facades\Cache::store('file')->has('test'); // Simple check

        // Recent Activity (Last 5 users registered)
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'created_at']);

        // Recent Orders
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return response()->json([
            'total_users' => User::count(),
            'total_instructors' => User::has('courses')->count(),
            'total_courses' => Course::count(),
            'total_revenue' => Order::sum('total'),
            'pending_courses' => Course::where('status', 'pending')->count(),
            'system_health' => [
                'database' => $dbStatus,
                'cache' => true,
                'server_load' => function_exists('sys_getloadavg') ? (sys_getloadavg()[0] ?? 0) : 0,
                'disk_free' => @disk_free_space(base_path()) ?: 0,
                'disk_total' => @disk_total_space(base_path()) ?: 0
            ],
            'recent_activity' => [
                'users' => $recentUsers,
                'orders' => $recentOrders
            ]
        ]);
    }

    // User Management
    public function users(Request $request)
    {
        $query = User::latest();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    // Ban/Unban User
    public function toggleBan(Request $request, User $user)
    {
        // Prevent admin from banning themselves
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot ban yourself'], 403);
        }

        $user->is_banned = !$user->is_banned;
        $user->save();

        if ($user->is_banned) {
            $user->tokens()->delete(); // Revoke all access tokens
        }

        return response()->json([
            'message' => $user->is_banned ? 'User banned successfully' : 'User unbanned successfully',
            'is_banned' => $user->is_banned
        ]);
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

    // Pending Instructors
    public function pendingInstructors()
    {
        $instructors = User::whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->where('instructor_verification_status', 'pending')->get();

        return response()->json($instructors);
    }

    // Verify Instructor
    public function verifyInstructor(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        if ($validated['action'] === 'approve') {
            $user->instructor_verification_status = 'approved';
        } else {
            $user->instructor_verification_status = 'rejected';
            // Ideally remove the role or just leave as rejected
        }
        $user->save();

        return response()->json(['message' => 'Instructor status updated', 'status' => $user->instructor_verification_status]);
    }
}
