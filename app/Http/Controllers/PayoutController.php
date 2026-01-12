<?php

namespace App\Http\Controllers;

use App\Models\InstructorEarning;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PayoutController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Get instructor earnings summary
     */
    public function earningsStats(Request $request)
    {
        $user = $request->user();

        // Total earnings
        $totalEarnings = InstructorEarning::where('instructor_id', $user->id)
            ->sum('net_amount');

        // Available for payout (status = available)
        $availableBalance = InstructorEarning::where('instructor_id', $user->id)
            ->where('status', 'available')
            ->sum('net_amount');

        // Pending (not yet available, e.g. within 14 day hold)
        $pendingEarnings = InstructorEarning::where('instructor_id', $user->id)
            ->where('status', 'pending')
            ->sum('net_amount');

        // Already paid out
        $paidOut = Payout::where('instructor_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Recent earnings
        $recentEarnings = InstructorEarning::where('instructor_id', $user->id)
            ->with('course:id,title')
            ->latest()
            ->take(10)
            ->get();

        // This month's earnings
        $thisMonthEarnings = InstructorEarning::where('instructor_id', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('net_amount');

        return response()->json([
            'total_earnings' => round($totalEarnings, 2),
            'available_balance' => round($availableBalance, 2),
            'pending_earnings' => round($pendingEarnings, 2),
            'paid_out' => round($paidOut, 2),
            'this_month' => round($thisMonthEarnings, 2),
            'recent_earnings' => $recentEarnings,
        ]);
    }

    /**
     * Get payout history
     */
    public function index(Request $request)
    {
        $payouts = Payout::where('instructor_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($payouts);
    }

    /**
     * Request a payout
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'amount' => 'required|numeric|min:50', // Minimum $50 payout
            'method' => 'required|in:paypal,bank_transfer,stripe',
        ]);

        // Check available balance
        $availableBalance = InstructorEarning::where('instructor_id', $user->id)
            ->where('status', 'available')
            ->sum('net_amount');

        if ($request->amount > $availableBalance) {
            return response()->json([
                'message' => 'Insufficient available balance. You have $' . round($availableBalance, 2) . ' available.',
            ], 422);
        }

        // Check for pending payout
        $pendingPayout = Payout::where('instructor_id', $user->id)
            ->whereIn('status', ['requested', 'processing'])
            ->exists();

        if ($pendingPayout) {
            return response()->json([
                'message' => 'You already have a pending payout request.',
            ], 422);
        }

        // Create payout request
        $payout = Payout::create([
            'instructor_id' => $user->id,
            'amount' => $request->amount,
            'method' => $request->method,
            'status' => 'requested',
        ]);

        // Mark earnings as paid (up to the requested amount)
        $this->markEarningsAsPaid($user->id, $request->amount);

        return response()->json([
            'message' => 'Payout requested successfully',
            'payout' => $payout,
        ], 201);
    }

    /**
     * Mark earnings as paid (deduct from available)
     */
    private function markEarningsAsPaid($instructorId, $amount)
    {
        $remaining = $amount;

        $earnings = InstructorEarning::where('instructor_id', $instructorId)
            ->where('status', 'available')
            ->oldest()
            ->get();

        foreach ($earnings as $earning) {
            if ($remaining <= 0)
                break;

            $earning->update(['status' => 'paid']);
            $remaining -= $earning->net_amount;
        }
    }
}
