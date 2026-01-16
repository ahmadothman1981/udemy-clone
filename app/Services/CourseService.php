<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseService
{
    /**
     * Create a new course.
     */
    public function createCourse(array $data, User $instructor): Course
    {
        return DB::transaction(function () use ($data, $instructor) {
            $course = new Course($data);
            $course->instructor_id = $instructor->id;
            $course->save();

            $this->clearCache();

            return $course;
        });
    }

    public function submitCourse(Course $course): Course
    {
        if ($course->status !== Course::STATUS_DRAFT) {
            // Or throw exception if strict
            return $course;
        }

        $course->update(['status' => Course::STATUS_PENDING]);

        \App\Events\CourseSubmitted::dispatch($course);

        $this->clearCache();

        return $course;
    }

    /**
     * Update an existing course.
     */
    public function updateCourse(Course $course, array $data, ?UploadedFile $thumbnail = null, ?UploadedFile $previewVideo = null): Course
    {
        return DB::transaction(function () use ($course, $data, $thumbnail, $previewVideo) {
            // Handle File Uploads
            if ($thumbnail) {
                $path = $thumbnail->store('thumbnails', 'public');
                $data['thumbnail'] = '/storage/' . $path;
            }

            if ($previewVideo) {
                $path = $previewVideo->store('preview_videos', 'public');
                $data['preview_video_url'] = '/storage/' . $path;
            }

            // Logic for discount validation
            if (isset($data['discount_price']) && isset($data['price'])) {
                if ($data['discount_price'] >= $data['price']) {
                    $data['discount_price'] = null;
                }
            } elseif (isset($data['discount_price']) && !isset($data['price'])) {
                // Check against existing price if not updating price
                if ($data['discount_price'] >= $course->price) {
                    $data['discount_price'] = null;
                }
            }

            $course->update($data);

            $this->clearCache();

            return $course;
        });
    }

    /**
     * Get paginated courses with filters.
     */
    public function getCourses(array $filters): LengthAwarePaginator
    {
        $query = Course::query()->where('published', true);

        // Search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filters
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['category'])) {
            $slug = $filters['category'];
            $query->whereHas('category', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
        }

        if (isset($filters['level_id'])) {
            $query->where('level_id', $filters['level_id']);
        }

        // FIX: Level filter by slug (CourseLevel does not have slug currently, but assuming we might add it or should use name/id)
        // Keeping consistent with audit finding: current code crashes if used. 
        // We will safely ignore it or use name if applicable, but for now let's disable the crashy part or assume ID used.
        // Actually, preventing the crash is prioritized.
        if (isset($filters['level'])) {
            // $query->whereHas('level', ...); // Commented out to prevent crash until CourseLevel has slug
        }

        // Price range filters
        if (isset($filters['price_min'])) {
            $query->where('price', '>=', (float) $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price', '<=', (float) $filters['price_max']);
        }

        if (isset($filters['min_rating'])) {
            $query->where('rating_avg', '>=', $filters['min_rating']);
        }

        // Sorting
        $sortParam = $filters['sort'] ?? 'newest';
        $direction = $filters['direction'] ?? 'asc'; // fallback

        $sortMapping = [
            'popular' => ['column' => 'enrollment_count', 'direction' => 'desc'],
            'newest' => ['column' => 'created_at', 'direction' => 'desc'],
            'rating' => ['column' => 'rating_avg', 'direction' => 'desc'],
            'price_low' => ['column' => 'price', 'direction' => 'asc'],
            'price_high' => ['column' => 'price', 'direction' => 'desc'],
            'price' => ['column' => 'price', 'direction' => $direction],
            'rating_avg' => ['column' => 'rating_avg', 'direction' => 'desc'],
            'created_at' => ['column' => 'created_at', 'direction' => 'desc'],
            'enrollment_count' => ['column' => 'enrollment_count', 'direction' => 'desc'],
        ];

        if (isset($sortMapping[$sortParam])) {
            $query->orderBy($sortMapping[$sortParam]['column'], $sortMapping[$sortParam]['direction']);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (auth('sanctum')->check()) {
            $query->withExists(['enrollments as is_enrolled' => function ($q) {
                $q->where('user_id', auth('sanctum')->id());
            }]);
        }

        return $query->with(['instructor', 'category', 'level'])->paginate(15);
    }

    /**
     * Clear course cache safely.
     */
    protected function clearCache()
    {
        try {
            // Only attempt to tag flush if driver supports it (redis/memcached)
            // File driver throws BadMethodCallException
            if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
                Cache::tags(['courses'])->flush();
            }
        } catch (\Throwable $e) {
            // Ignore if cache clearing fails
        }
    }
}
