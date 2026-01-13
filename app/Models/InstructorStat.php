<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorStat extends Model
{
    protected $fillable = [
        'instructor_id',
        'total_students',
        'total_revenue',
        'average_rating',
        'total_reviews',
        'course_count',
        'monthly_students',
        'monthly_revenue',
        'monthly_reviews',
        'unanswered_questions',
        'stats_date',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'monthly_revenue' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'stats_date' => 'date',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the latest stats for an instructor
     */
    public static function latestFor(int $instructorId): ?self
    {
        return self::where('instructor_id', $instructorId)
            ->orderBy('stats_date', 'desc')
            ->first();
    }
}
