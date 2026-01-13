<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;

class CourseController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
        ];
    }

    public function index(Request $request)
    {
        // Generate a unique cache key based on query parameters
        $cacheKey = 'courses_idx_' . md5(json_encode($request->all()));

        $callback = function () use ($request) {
            return $this->courseService->getCourses($request->all());
        };

        // Safe Cache Retrieval
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                return CourseResource::collection(
                    Cache::tags(['courses'])->remember($cacheKey, 60 * 15, $callback)
                );
            }
        } catch (\Throwable $e) {
        }

        // Fallback: Use standard cache or no cache if tags fail
        // Using "remember" without tags
        $courses = Cache::remember($cacheKey, 60 * 5, $callback);

        return CourseResource::collection($courses);
    }

    public function show(Request $request, Course $course)
    {
        $course->load(['instructor', 'category', 'sections.lectures']);
        return new CourseResource($course);
    }

    public function store(StoreCourseRequest $request)
    {
        $this->authorize('create', Course::class);

        $course = $this->courseService->createCourse(
            $request->validated(),
            $request->user()
        );

        return new CourseResource($course);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'discount_price' => 'nullable|numeric',
            'published' => 'sometimes|boolean',
            'language' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'level_id' => 'sometimes|exists:course_levels,id',
            'subtitle' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|image|max:2048',
            'preview_video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:512000',
        ]);

        $course = $this->courseService->updateCourse(
            $course,
            $validated,
            $request->file('thumbnail'),
            $request->file('preview_video')
        );

        return new CourseResource($course);
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        // Clear cache manually usually needed here too, likely inside delete logic or service.
        // For now, flush tags if possible
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                Cache::tags(['courses'])->flush();
            }
        } catch (\Throwable $e) {
        }

        return response()->noContent();
    }
}
