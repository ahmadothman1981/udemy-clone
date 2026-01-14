<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Order;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\CourseQuestion;
use App\Models\CourseAnswer;
use App\Models\Section;
use App\Models\Lecture;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    // ==========================================
    // PLATFORM STATS & ANALYTICS
    // ==========================================

    public function stats()
    {
        $dbStatus = true;
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = false;
        }

        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'created_at']);
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return response()->json([
            'total_users' => User::count(),
            'total_instructors' => User::has('courses')->count(),
            'total_courses' => Course::count(),
            'total_revenue' => Order::sum('total'),
            'pending_courses' => Course::where('status', 'pending')->count(),
            'pending_instructors' => User::where('instructor_verification_status', 'pending')->count(),
            'total_enrollments' => Enrollment::count(),
            'total_reviews' => Review::count(),
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

    public function analytics(Request $request)
    {
        $days = $request->get('days', 30);

        // New users per day
        $newUsersDaily = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Enrollments trend
        $enrollmentsTrend = Enrollment::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue trend
        $revenueTrend = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top selling courses
        $topCourses = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(10)
            ->get(['id', 'title', 'price', 'instructor_id']);

        // Average ratings by category (from reviews table)
        $ratingsByCategory = DB::table('reviews')
            ->join('courses', 'reviews.course_id', '=', 'courses.id')
            ->join('categories', 'courses.category_id', '=', 'categories.id')
            ->select('categories.id as category_id', 'categories.name as category_name', DB::raw('AVG(reviews.rating) as avg_rating'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Completion rates (based on completed_at)
        $totalEnrollments = DB::table('enrollments')->count();
        $completedEnrollments = DB::table('enrollments')->whereNotNull('completed_at')->count();
        $avgCompletionRate = $totalEnrollments > 0 ? ($completedEnrollments / $totalEnrollments) * 100 : 0;

        return response()->json([
            'new_users_daily' => $newUsersDaily,
            'enrollments_trend' => $enrollmentsTrend,
            'revenue_trend' => $revenueTrend,
            'top_courses' => $topCourses,
            'ratings_by_category' => $ratingsByCategory,
            'avg_completion_rate' => round($avgCompletionRate, 1),
        ]);
    }

    // ==========================================
    // USER MANAGEMENT
    // ==========================================

    public function users(Request $request)
    {
        $query = User::with('roles')->latest();

        // Search
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        return response()->json($query->paginate(20));
    }

    public function showUser(User $user)
    {
        return response()->json($user->load(['roles', 'courses', 'enrollments']));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|nullable|string',
            'bio' => 'sometimes|nullable|string',
            'language' => 'sometimes|string|max:10',
            'country' => 'sometimes|nullable|string|max:100',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()
        ]);
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:promote_to_instructor,grant_admin,revoke_admin,revoke_instructor'
        ]);

        // Prevent self-demotion
        if ($user->id === $request->user()->id && in_array($validated['action'], ['revoke_admin'])) {
            return response()->json(['message' => 'Cannot modify your own admin role'], 403);
        }

        switch ($validated['action']) {
            case 'promote_to_instructor':
                $instructorRole = Role::where('name', 'instructor')->first();
                if ($instructorRole && !$user->roles->contains($instructorRole->id)) {
                    $user->roles()->attach($instructorRole->id);
                    $user->update(['instructor_verification_status' => 'approved']);
                }
                break;

            case 'grant_admin':
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole && !$user->roles->contains($adminRole->id)) {
                    $user->roles()->attach($adminRole->id);
                }
                break;

            case 'revoke_admin':
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $user->roles()->detach($adminRole->id);
                }
                break;

            case 'revoke_instructor':
                $instructorRole = Role::where('name', 'instructor')->first();
                if ($instructorRole) {
                    $user->roles()->detach($instructorRole->id);
                }
                break;
        }

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->fresh()->load('roles')
        ]);
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,deactivated,banned'
        ]);

        // Prevent self-ban
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot change your own status'], 403);
        }

        $user->status = $validated['status'];
        $user->save();

        // Revoke tokens if banned or deactivated
        if (in_array($validated['status'], ['banned', 'deactivated'])) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'User status updated to ' . $validated['status'],
            'status' => $user->status
        ]);
    }

    // Legacy toggle ban (for backwards compatibility)
    public function toggleBan(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot ban yourself'], 403);
        }

        $newStatus = $user->status === 'banned' ? 'active' : 'banned';
        $user->status = $newStatus;
        $user->save();

        if ($newStatus === 'banned') {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => $newStatus === 'banned' ? 'User banned successfully' : 'User unbanned successfully',
            'status' => $user->status
        ]);
    }

    // ==========================================
    // INSTRUCTOR MANAGEMENT
    // ==========================================

    public function instructors(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->with(['roles', 'courses']);

        // Search
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Verification status filter
        if ($request->has('verification_status')) {
            $query->where('instructor_verification_status', $request->verification_status);
        }

        $instructors = $query->paginate(20);

        // Add stats to each instructor
        $instructors->getCollection()->transform(function ($instructor) {
            $courseIds = $instructor->courses->pluck('id');
            $instructor->stats = [
                'course_count' => $instructor->courses->count(),
                'student_count' => Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id'),
                'avg_rating' => Course::whereIn('id', $courseIds)->avg('rating') ?? 0,
                'total_revenue' => Order::whereHas('items', function ($q) use ($courseIds) {
                    $q->whereIn('course_id', $courseIds);
                })->sum('total'),
            ];
            return $instructor;
        });

        return response()->json($instructors);
    }

    public function instructorStats(User $user)
    {
        $courseIds = $user->courses->pluck('id');

        return response()->json([
            'course_count' => $user->courses->count(),
            'student_count' => Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id'),
            'avg_rating' => Course::whereIn('id', $courseIds)->avg('rating') ?? 0,
            'total_revenue' => Order::whereHas('items', function ($q) use ($courseIds) {
                $q->whereIn('course_id', $courseIds);
            })->sum('total'),
            'courses' => $user->courses()->withCount('enrollments')->get(),
            'restrictions' => $user->instructor_restrictions ?? [],
        ]);
    }

    public function restrictInstructor(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:block_new_courses,block_new_students,full_restrict,unrestrict'
        ]);

        $restrictions = $user->instructor_restrictions ?? [];

        switch ($validated['action']) {
            case 'block_new_courses':
                if (!in_array('block_new_courses', $restrictions)) {
                    $restrictions[] = 'block_new_courses';
                }
                break;
            case 'block_new_students':
                if (!in_array('block_new_students', $restrictions)) {
                    $restrictions[] = 'block_new_students';
                }
                break;
            case 'full_restrict':
                $restrictions = ['block_new_courses', 'block_new_students'];
                break;
            case 'unrestrict':
                $restrictions = [];
                break;
        }

        $user->instructor_restrictions = $restrictions;
        $user->save();

        return response()->json([
            'message' => 'Instructor restrictions updated',
            'restrictions' => $user->instructor_restrictions
        ]);
    }

    public function pendingInstructors()
    {
        $instructors = User::whereHas('roles', function ($q) {
            $q->where('name', 'instructor');
        })->where('instructor_verification_status', 'pending')
            ->with('roles')
            ->get();

        return response()->json($instructors);
    }

    public function verifyInstructor(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|nullable|string'
        ]);

        if ($validated['action'] === 'approve') {
            $user->instructor_verification_status = 'approved';
        } else {
            $user->instructor_verification_status = 'rejected';
        }
        $user->save();

        return response()->json([
            'message' => 'Instructor status updated',
            'status' => $user->instructor_verification_status
        ]);
    }

    // ==========================================
    // COURSE MANAGEMENT
    // ==========================================

    public function courses(Request $request)
    {
        $query = Course::with(['instructor', 'category'])->latest();

        // Search
        if ($request->has('q') && $request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Instructor filter
        if ($request->has('instructor_id') && $request->instructor_id) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Include hidden courses
        if ($request->has('include_hidden')) {
            $query->orWhere('admin_hidden', true);
        }

        return response()->json($query->paginate(20));
    }

    public function pendingCourses()
    {
        $courses = Course::where('status', 'pending')
            ->with('instructor', 'category')
            ->get();
        return response()->json($courses);
    }

    public function approveCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'required_if:action,reject|nullable|string'
        ]);

        if ($validated['action'] === 'approve') {
            $course->update(['status' => 'published', 'published' => true]);
        } else {
            $course->update(['status' => 'rejected']);
            // TODO: Send email to instructor with reason
        }

        return response()->json(['message' => 'Course ' . $validated['action'] . 'd successfully']);
    }

    public function updateCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'sometimes|nullable|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'language' => 'sometimes|string|max:10',
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course->fresh()
        ]);
    }

    public function hideCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $course->update([
            'admin_hidden' => true,
            'hidden_reason' => $validated['reason']
        ]);

        return response()->json(['message' => 'Course hidden successfully']);
    }

    public function restoreCourse(Course $course)
    {
        $course->update([
            'admin_hidden' => false,
            'hidden_reason' => null
        ]);

        return response()->json(['message' => 'Course restored successfully']);
    }

    public function removeSection(Course $course, Section $section)
    {
        if ($section->course_id !== $course->id) {
            return response()->json(['message' => 'Section does not belong to this course'], 404);
        }

        $section->delete();

        return response()->json(['message' => 'Section removed successfully']);
    }

    public function removeLecture(Course $course, Lecture $lecture)
    {
        $section = Section::find($lecture->section_id);
        if (!$section || $section->course_id !== $course->id) {
            return response()->json(['message' => 'Lecture does not belong to this course'], 404);
        }

        $lecture->delete();

        return response()->json(['message' => 'Lecture removed successfully']);
    }

    // ==========================================
    // ENROLLMENT MANAGEMENT
    // ==========================================

    public function enrollments(Request $request)
    {
        $query = Enrollment::with(['user', 'course'])->latest();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        return response()->json($query->paginate(20));
    }

    public function addEnrollment(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'reason' => 'sometimes|string|max:500'
        ]);

        // Check if already enrolled
        $exists = Enrollment::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'User is already enrolled in this course'], 422);
        }

        $enrollment = Enrollment::create([
            'user_id' => $validated['user_id'],
            'course_id' => $validated['course_id'],
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Enrollment created successfully',
            'enrollment' => $enrollment->load(['user', 'course'])
        ]);
    }

    public function removeEnrollment(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:500'
        ]);

        $enrollment->delete();

        return response()->json(['message' => 'Enrollment removed successfully']);
    }

    public function studentActivity(Request $request)
    {
        $query = User::with(['enrollments.course'])
            ->has('enrollments')
            ->latest();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20);

        $students->getCollection()->transform(function ($student) {
            $student->activity_stats = [
                'enrolled_courses' => $student->enrollments->count(),
                'avg_progress' => $student->enrollments->avg('progress') ?? 0,
                'completed_courses' => $student->enrollments->where('progress', 100)->count(),
            ];
            return $student;
        });

        return response()->json($students);
    }

    // ==========================================
    // REVIEW & Q&A MODERATION
    // ==========================================

    public function reviews(Request $request)
    {
        $query = Review::with(['user', 'course'])->latest();

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->has('instructor_id')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('instructor_id', $request->instructor_id);
            });
        }

        return response()->json($query->paginate(20));
    }

    public function removeReview(Request $request, Review $review)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:500'
        ]);

        $review->delete();

        return response()->json(['message' => 'Review removed successfully']);
    }

    public function questions(Request $request)
    {
        $query = CourseQuestion::with(['user', 'course', 'answers'])->latest();

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('answered')) {
            if ($request->answered === 'true') {
                $query->has('answers');
            } else {
                $query->doesntHave('answers');
            }
        }

        return response()->json($query->paginate(20));
    }

    public function removeQuestion(CourseQuestion $question)
    {
        $question->answers()->delete();
        $question->delete();

        return response()->json(['message' => 'Question and answers removed successfully']);
    }

    public function removeAnswer(CourseAnswer $answer)
    {
        $answer->delete();

        return response()->json(['message' => 'Answer removed successfully']);
    }

    public function toggleCourseQnA(Course $course)
    {
        $course->qna_enabled = !$course->qna_enabled;
        $course->save();

        return response()->json([
            'message' => $course->qna_enabled ? 'Q&A enabled' : 'Q&A disabled',
            'qna_enabled' => $course->qna_enabled
        ]);
    }
}
