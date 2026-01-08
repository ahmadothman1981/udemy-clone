<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'thumbnail',
        'price',
        'discount_price',
        'level_id',
        'language',
        'estimated_hours',
        'instructor_id',
        'category_id',
        'subcategory_id',
        'rating_avg',
        'enrollment_count',
        'published',
        'published_at',
        'status',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($course) {
            if (empty($course->slug)) {
                $course->slug = \Illuminate\Support\Str::slug($course->title) . '-' . $course->id;
            }
        });

        // Ensure slug is generated if title changes or on create
        static::creating(function ($course) {
            $course->slug = \Illuminate\Support\Str::slug($course->title);
            // Verify uniqueness or append ID/random in real world, 
            // but for now simple slug. To be safe, maybe append random if exists?
            // Simplest: just slug. If duplicate, DB throws error (unique constraint).
            // Let's rely on simple slug for now, or append uniqid if needed.
            // User requested "slug course name".
            // We can tackle duplicates if they arise, or append a short random string.
            // Re-saving to append ID is tricky on 'creating' since ID is null.
        });

        static::created(function ($course) {
            // If we want ID in slug, we do it here.
            // "slug course name" - usually just title-slug.
            // If collision, maybe title-slug-id.
            // Let's stick to title-slug.
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)
            ->orWhere('id', $value)
            ->firstOrFail();
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function level()
    {
        return $this->belongsTo(CourseLevel::class, 'level_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function lectures()
    {
        return $this->hasManyThrough(Lecture::class, Section::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function questions()
    {
        return $this->hasMany(CourseQuestion::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function isEnrolledBy(?User $user)
    {
        if (!$user) {
            return false;
        }
        return $this->enrollments()->where('user_id', $user->id)->exists();
    }
}
