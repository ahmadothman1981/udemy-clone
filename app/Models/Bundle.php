<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'original_price',
        'thumbnail',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Courses in this bundle
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'bundle_courses')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /**
     * Calculate savings percentage
     */
    public function getSavingsPercentAttribute()
    {
        if (!$this->original_price || $this->original_price == 0)
            return 0;
        return round((1 - ($this->price / $this->original_price)) * 100);
    }

    /**
     * Calculate original price from courses
     */
    public function calculateOriginalPrice()
    {
        return $this->courses->sum('price');
    }

    /**
     * Route model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
