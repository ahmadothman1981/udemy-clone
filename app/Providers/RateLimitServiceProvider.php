<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // General API rate limit: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict limit for auth endpoints: 5 attempts per minute
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again later.',
                ], 429);
            });
        });

        // Limit for password reset: 3 attempts per minute
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'Too many password reset attempts. Please wait a minute.',
                ], 429);
            });
        });

        // Limit for registration: 3 per minute to prevent bot signups
        RateLimiter::for('registration', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json([
                    'message' => 'Too many registration attempts. Please try again later.',
                ], 429);
            });
        });

        // Limit for checkout/payment: 10 per minute
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Limit for file uploads: 5 per minute
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
