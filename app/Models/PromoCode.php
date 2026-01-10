<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type', // 'percentage' or 'fixed'
        'discount_value',
        'min_purchase',
        'max_uses',
        'used_count',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    /**
     * Check if promo code is valid
     */
    public function isValid($orderTotal = 0)
    {
        if (!$this->active) {
            return ['valid' => false, 'message' => 'This promo code is inactive'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'This promo code has expired'];
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return ['valid' => false, 'message' => 'This promo code has reached its usage limit'];
        }

        if ($this->min_purchase && $orderTotal < $this->min_purchase) {
            return ['valid' => false, 'message' => "Minimum purchase of \${$this->min_purchase} required"];
        }

        return ['valid' => true, 'message' => 'Promo code applied'];
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($orderTotal)
    {
        if ($this->discount_type === 'percentage') {
            return round($orderTotal * ($this->discount_value / 100), 2);
        }

        // Fixed amount - can't exceed order total
        return min($this->discount_value, $orderTotal);
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage()
    {
        $this->increment('used_count');
    }
}
