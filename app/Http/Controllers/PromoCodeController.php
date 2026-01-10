<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PromoCodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * List all promo codes (admin only)
     */
    public function index(Request $request)
    {
        // TODO: Add admin check
        $promoCodes = PromoCode::orderBy('created_at', 'desc')->get();

        return response()->json($promoCodes);
    }

    /**
     * Create a new promo code (admin only)
     */
    public function store(Request $request)
    {
        // TODO: Add admin check
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
            'active' => 'boolean',
        ]);

        // Uppercase the code
        $validated['code'] = strtoupper($validated['code']);

        $promoCode = PromoCode::create($validated);

        return response()->json($promoCode, 201);
    }

    /**
     * Update a promo code (admin only)
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        // TODO: Add admin check
        $validated = $request->validate([
            'description' => 'nullable|string|max:255',
            'discount_type' => 'in:percentage,fixed',
            'discount_value' => 'numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $promoCode->update($validated);

        return response()->json($promoCode);
    }

    /**
     * Delete a promo code (admin only)
     */
    public function destroy(PromoCode $promoCode)
    {
        // TODO: Add admin check
        $promoCode->delete();

        return response()->json(['message' => 'Promo code deleted']);
    }

    /**
     * Validate a promo code at checkout (public for authenticated users)
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);

        $promoCode = PromoCode::where('code', strtoupper($request->code))->first();

        if (!$promoCode) {
            return response()->json([
                'valid' => false,
                'message' => 'Promo code not found',
            ], 404);
        }

        $validation = $promoCode->isValid($request->order_total);

        if (!$validation['valid']) {
            return response()->json($validation, 400);
        }

        $discount = $promoCode->calculateDiscount($request->order_total);

        return response()->json([
            'valid' => true,
            'message' => $validation['message'],
            'code' => $promoCode->code,
            'discount_type' => $promoCode->discount_type,
            'discount_value' => $promoCode->discount_value,
            'discount_amount' => $discount,
            'new_total' => max(0, $request->order_total - $discount),
        ]);
    }
}
