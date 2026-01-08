<?php

namespace App\Http\Controllers;

use App\Models\SavedPaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => $request->user()->savedPaymentMethods()->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:card,paypal',
            'email' => 'required_if:type,paypal|nullable|email',
            'brand' => 'required_if:type,card|nullable|string',
            'last4' => 'required_if:type,card|nullable|string|size:4',
            'exp_month' => 'required_if:type,card|nullable|integer|min:1|max:12',
            'exp_year' => 'required_if:type,card|nullable|integer|min:'.date('Y'),
        ]);

        $paymentMethod = $request->user()->savedPaymentMethods()->create([
            'type' => $request->type,
            'email' => $request->email,
            'brand' => $request->brand,
            'last4' => $request->last4,
            'exp_month' => $request->exp_month,
            'exp_year' => $request->exp_year,
            'is_default' => $request->boolean('is_default', false),
        ]);

        return response()->json([
            'message' => 'Payment method saved successfully',
            'data' => $paymentMethod
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $paymentMethod = $request->user()->savedPaymentMethods()->findOrFail($id);
        $paymentMethod->delete();

        return response()->json(['message' => 'Payment method deleted successfully']);
    }
}
