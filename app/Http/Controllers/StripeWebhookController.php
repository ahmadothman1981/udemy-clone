<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            default:
                // Unexpected event type
                return response()->json(['message' => 'Unhandled event type'], 200);
        }

        return response()->json(['message' => 'Webhook handled'], 200);
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);

        if (!$order || $order->status === 'paid') {
            return; // Already processed or not found
        }

        // Update order status
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Get order items and create enrollments
        $orderItems = OrderItem::where('order_id', $order->id)->get();

        foreach ($orderItems as $item) {
            // Check if enrollment already exists
            $existingEnrollment = Enrollment::where('user_id', $order->user_id)
                ->where('course_id', $item->course_id)
                ->first();

            if (!$existingEnrollment) {
                Enrollment::create([
                    'user_id' => $order->user_id,
                    'course_id' => $item->course_id,
                    'amount_paid' => $item->price,
                    'enrolled_at' => now(),
                ]);

                // Increment enrollment count
                Course::where('id', $item->course_id)->increment('enrollment_count');
            }
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'failed']);
        }
    }
}
