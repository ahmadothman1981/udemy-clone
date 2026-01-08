<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class CheckoutController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Preview checkout - get course details for given IDs
     */
    public function preview(Request $request)
    {
        $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        $courses = Course::whereIn('id', $request->course_ids)
            ->with('instructor:id,name')
            ->get(['id', 'title', 'slug', 'price', 'thumbnail', 'instructor_id']);

        // Check if user is already enrolled in any of these courses
        $enrolledCourseIds = Enrollment::where('user_id', $request->user()->id)
            ->whereIn('course_id', $request->course_ids)
            ->pluck('course_id')
            ->toArray();

        $items = $courses->map(function ($course) use ($enrolledCourseIds) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'price' => $course->price,
                'thumbnail' => $course->thumbnail,
                'instructor_name' => $course->instructor?->name ?? 'Instructor',
                'already_enrolled' => in_array($course->id, $enrolledCourseIds),
            ];
        });

        // Filter out already enrolled courses
        $purchasableItems = $items->filter(fn($item) => !$item['already_enrolled']);

        $subtotal = $purchasableItems->sum('price');

        return response()->json([
            'items' => $purchasableItems->values(),
            'already_enrolled' => $items->filter(fn($item) => $item['already_enrolled'])->values(),
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2), // No discounts for now
        ]);
    }

    /**
     * Process checkout - create order, mock payment, create enrollments
     */
    public function processOrder(Request $request)
    {
        $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
            'payment_method_id' => 'nullable|integer|exists:saved_payment_methods,id',
        ]);

        $user = $request->user();

        // Get courses (excluding already enrolled)
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)
            ->whereIn('course_id', $request->course_ids)
            ->pluck('course_id')
            ->toArray();

        $courses = Course::whereIn('id', $request->course_ids)
            ->whereNotIn('id', $enrolledCourseIds)
            ->get();

        if ($courses->isEmpty()) {
            return response()->json([
                'message' => 'No new courses to purchase. You may already be enrolled.',
            ], 400);
        }

        $total = $courses->sum('price');

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'total' => $total,
            'status' => 'pending',
            'payment_method' => $request->payment_method_id ? 'saved_card' : 'new_card',
        ]);

        // Create order items
        foreach ($courses as $course) {
            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $course->id,
                'price_snapshot' => $course->price,
                'price' => $course->price,
            ]);
        }

        // Mock payment processing (in real app, use Stripe PaymentIntent)
        // For demonstration, we'll simulate success
        $paymentSuccess = true;

        if ($paymentSuccess) {
            // Update order status
            $order->update(['status' => 'paid']);

            // Create enrollments for each course
            foreach ($courses as $course) {
                Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'amount_paid' => $course->price,
                    'enrolled_at' => now(),
                ]);

                // Increment enrollment count
                $course->increment('enrollment_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully!',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'courses' => $courses->map(fn($c) => [
                        'id' => $c->id,
                        'title' => $c->title,
                        'slug' => $c->slug,
                    ]),
                ],
            ], 201);
        } else {
            $order->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.',
            ], 400);
        }
    }
}
