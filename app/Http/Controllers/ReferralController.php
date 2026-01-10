<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReferralController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * Get or create user's referral code
     */
    public function getCode(Request $request)
    {
        $user = $request->user();

        $referral = Referral::where('referrer_id', $user->id)
            ->whereNull('referred_user_id')
            ->first();

        if (!$referral) {
            $referral = Referral::create([
                'referrer_id' => $user->id,
                'code' => Referral::generateCode($user),
                'commission_rate' => 10.00, // 10% default
            ]);
        }

        return response()->json([
            'code' => $referral->code,
            'referral_link' => config('app.url') . '/register?ref=' . $referral->code,
            'commission_rate' => $referral->commission_rate,
        ]);
    }

    /**
     * Get referral stats
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->whereNotNull('referred_user_id')
            ->with('referredUser:id,name,created_at')
            ->get();

        $totalEarned = Referral::where('referrer_id', $user->id)->sum('total_earned');
        $pendingCommissions = $user->referralCommissions()
            ->where('status', 'pending')
            ->sum('amount');

        return response()->json([
            'total_referrals' => $referrals->count(),
            'total_earned' => number_format($totalEarned, 2),
            'pending_earnings' => number_format($pendingCommissions, 2),
            'referrals' => $referrals,
        ]);
    }

    /**
     * Apply referral code during registration
     */
    public static function applyReferral(string $code, int $userId): bool
    {
        $referral = Referral::where('code', strtoupper($code))
            ->whereNull('referred_user_id')
            ->first();

        if (!$referral) {
            return false;
        }

        // Clone the referral for the new user (keeping original for future referrals)
        Referral::create([
            'referrer_id' => $referral->referrer_id,
            'referred_user_id' => $userId,
            'code' => $referral->code,
            'commission_rate' => $referral->commission_rate,
            'status' => 'active',
        ]);

        return true;
    }
}
