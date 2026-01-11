<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
        ];
    }

    public function index(Request $request)
    {
        $query = Course::query()->where('published', true);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('category')) {
            $slug = $request->category;
            $query->whereHas('category', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        if ($request->has('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        // Level filter by slug
        if ($request->has('level')) {
            $levelSlug = $request->level;
            $query->whereHas('level', function ($q) use ($levelSlug) {
                $q->where('slug', $levelSlug);
            });
        }

        // Price range filters
        if ($request->has('price_min')) {
            $query->where('price', '>=', (float) $request->price_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', (float) $request->price_max);
        }

        if ($request->has('min_rating')) {
            $query->where('rating_avg', '>=', $request->min_rating);
        }

        // Sorting - map frontend values to database columns
        $sortParam = $request->input('sort', 'newest');

        $sortMapping = [
            'popular' => ['column' => 'enrollment_count', 'direction' => 'desc'],
            'newest' => ['column' => 'created_at', 'direction' => 'desc'],
            'rating' => ['column' => 'rating_avg', 'direction' => 'desc'],
            'price_low' => ['column' => 'price', 'direction' => 'asc'],
            'price_high' => ['column' => 'price', 'direction' => 'desc'],
            // Legacy support for direct column names
            'price' => ['column' => 'price', 'direction' => $request->input('direction', 'asc')],
            'rating_avg' => ['column' => 'rating_avg', 'direction' => 'desc'],
            'created_at' => ['column' => 'created_at', 'direction' => 'desc'],
            'enrollment_count' => ['column' => 'enrollment_count', 'direction' => 'desc'],
        ];

        if (isset($sortMapping[$sortParam])) {
            $query->orderBy($sortMapping[$sortParam]['column'], $sortMapping[$sortParam]['direction']);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $courses = $query->with(['instructor', 'category', 'level'])->paginate(15);

        return CourseResource::collection($courses);
    }

    public function show(Request $request, Course $course)
    {
        // Load relationships
        $course->load(['instructor', 'category', 'sections.lectures']);

        // Hide content via Resource logic if needed, but passing user context is good.
        // The Resource handles protecting video_url based on enrollment.

        return new CourseResource($course);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'language' => 'required|string',
            'level_id' => 'required|exists:course_levels,id', // using level_id FK
        ]);

        $course = new Course($validated);
        $course->instructor_id = $request->user()->id;
        $course->save();

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
            'thumbnail' => 'nullable|image|max:2048', // For file upload handling via proper means or if handling here
            // Note: Update usually handles multipart differently or uses separate endpoint for files if not using _method=PUT
            // The frontend sends _method=PUT so Request captures it.
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }

        // Logic check: Ensure discount is valid (less than price)
        if (isset($validated['discount_price']) && isset($validated['price'])) {
            if ($validated['discount_price'] >= $validated['price']) {
                $validated['discount_price'] = null;
            }
        } elseif (isset($validated['discount_price']) && !isset($validated['price'])) {
            // If price not updated, check against DB
            if ($validated['discount_price'] >= $course->price) {
                $validated['discount_price'] = null;
            }
        }

        $course->update($validated);

        return new CourseResource($course);
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        return response()->noContent();
    }
}
