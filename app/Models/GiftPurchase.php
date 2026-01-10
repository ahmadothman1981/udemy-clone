<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GiftPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'recipient_email',
        'recipient_name',
        'course_id',
        'bundle_id',
        'order_id',
        'redemption_code',
        'message',
        'redeemed_at',
        'redeemed_by',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    /**
     * Generate unique redemption code
     */
    public static function generateCode(): string
    {
        do {
            $code = 'GIFT-' . strtoupper(Str::random(8));
        } while (self::where('redemption_code', $code)->exists());

        return $code;
    }

    /**
     * Buyer of the gift
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Course being gifted
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Bundle being gifted
     */
    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * User who redeemed the gift
     */
    public function redeemer()
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    /**
     * Check if gift can be redeemed
     */
    public function isRedeemable(): bool
    {
        return is_null($this->redeemed_at);
    }

    /**
     * Redeem the gift for a user
     */
    public function redeem(User $user): bool
    {
        if (!$this->isRedeemable()) {
            return false;
        }

        $this->update([
            'redeemed_at' => now(),
            'redeemed_by' => $user->id,
        ]);

        // Create enrollment
        if ($this->course_id) {
            Enrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $this->course_id,
            ]);
        }

        // If bundle, enroll in all courses
        if ($this->bundle_id) {
            $bundle = $this->bundle;
            foreach ($bundle->courses as $course) {
                Enrollment::firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);
            }
        }

        return true;
    }
}
