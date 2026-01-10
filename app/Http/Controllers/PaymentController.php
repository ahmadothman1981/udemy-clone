<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function createIntent(Request $request, Course $course)
    {
        // Integration with Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $course->price * 100, // cents
            'currency' => 'usd', // or dynamic
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
            ],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }
}
