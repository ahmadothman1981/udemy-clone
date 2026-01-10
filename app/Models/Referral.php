<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'code',
        'commission_rate', // percentage
        'status', // pending, active, paid
        'total_earned',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earned' => 'decimal:2',
    ];

    /**
     * Generate unique referral code
     */
    public static function generateCode(User $user): string
    {
        $base = strtoupper(substr($user->name, 0, 3));
        $code = $base . strtoupper(Str::random(5));

        while (self::where('code', $code)->exists()) {
            $code = $base . strtoupper(Str::random(5));
        }

        return $code;
    }

    /**
     * The referrer (affiliate)
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * User who was referred
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Commissions earned from this referral
     */
    public function commissions()
    {
        return $this->hasMany(ReferralCommission::class);
    }
}
