<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WishlistController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Get user's wishlist
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wishlistIds = $user->preferences['wishlist'] ?? [];

        $courses = Course::whereIn('id', $wishlistIds)
            ->with(['instructor:id,name', 'category:id,name'])
            ->get(['id', 'title', 'slug', 'price', 'discount_price', 'thumbnail', 'instructor_id', 'category_id', 'rating_avg']);

        return response()->json([
            'items' => $courses,
            'count' => $courses->count(),
        ]);
    }

    /**
     * Add a course to wishlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = $request->user();
        $wishlist = $user->preferences['wishlist'] ?? [];

        if (!in_array($request->course_id, $wishlist)) {
            $wishlist[] = $request->course_id;
            $user->preferences = array_merge($user->preferences ?? [], ['wishlist' => $wishlist]);
            $user->save();
        }

        return response()->json([
            'message' => 'Added to wishlist',
            'wishlist' => $wishlist,
        ]);
    }

    /**
     * Remove a course from wishlist
     */
    public function destroy(Request $request, int $courseId)
    {
        $user = $request->user();
        $wishlist = $user->preferences['wishlist'] ?? [];

        $wishlist = array_values(array_filter($wishlist, fn($id) => $id != $courseId));
        $user->preferences = array_merge($user->preferences ?? [], ['wishlist' => $wishlist]);
        $user->save();

        return response()->json([
            'message' => 'Removed from wishlist',
            'wishlist' => $wishlist,
        ]);
    }

    /**
     * Check if a course is in wishlist
     */
    public function check(Request $request, int $courseId)
    {
        $user = $request->user();
        $wishlist = $user->preferences['wishlist'] ?? [];

        return response()->json([
            'in_wishlist' => in_array($courseId, $wishlist),
        ]);
    }
}
