<?php

namespace App\Http\Controllers;

use App\Models\GiftPurchase;
use App\Models\Course;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;

class GiftController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Create a gift purchase
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email',
            'recipient_name' => 'nullable|string|max:255',
            'course_id' => 'required_without:bundle_id|exists:courses,id',
            'bundle_id' => 'required_without:course_id|exists:bundles,id',
            'message' => 'nullable|string|max:500',
        ]);

        $gift = GiftPurchase::create([
            'buyer_id' => $request->user()->id,
            'recipient_email' => $validated['recipient_email'],
            'recipient_name' => $validated['recipient_name'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'bundle_id' => $validated['bundle_id'] ?? null,
            'redemption_code' => GiftPurchase::generateCode(),
            'message' => $validated['message'] ?? null,
        ]);

        // TODO: Send email with redemption code

        return response()->json([
            'message' => 'Gift created successfully',
            'gift' => $gift,
            'redemption_code' => $gift->redemption_code,
        ], 201);
    }

    /**
     * Redeem a gift
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $gift = GiftPurchase::where('redemption_code', strtoupper($request->code))->first();

        if (!$gift) {
            return response()->json(['message' => 'Invalid redemption code'], 404);
        }

        if (!$gift->isRedeemable()) {
            return response()->json(['message' => 'This gift has already been redeemed'], 400);
        }

        $success = $gift->redeem($request->user());

        if ($success) {
            return response()->json([
                'message' => 'Gift redeemed successfully!',
                'course' => $gift->course,
                'bundle' => $gift->bundle,
            ]);
        }

        return response()->json(['message' => 'Failed to redeem gift'], 500);
    }

    /**
     * List gifts sent by user
     */
    public function sentGifts(Request $request)
    {
        $gifts = GiftPurchase::where('buyer_id', $request->user()->id)
            ->with(['course:id,title,slug', 'bundle:id,title,slug'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($gifts);
    }

    /**
     * List gifts received by user
     */
    public function receivedGifts(Request $request)
    {
        $gifts = GiftPurchase::where('redeemed_by', $request->user()->id)
            ->with(['course:id,title,slug', 'bundle:id,title,slug', 'buyer:id,name'])
            ->orderBy('redeemed_at', 'desc')
            ->get();

        return response()->json($gifts);
    }
}
