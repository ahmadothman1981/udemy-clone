<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'criteria_type', // 'courses_completed', 'reviews_given', 'days_streak', etc.
        'criteria_value',
        'color',
    ];

    /**
     * Users who have earned this badge
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Check if a user qualifies for this badge
     */
    public function checkEligibility(User $user): bool
    {
        switch ($this->criteria_type) {
            case 'courses_completed':
                return $user->enrollments()
                    ->whereNotNull('completed_at')
                    ->count() >= $this->criteria_value;

            case 'reviews_given':
                return $user->reviews()->count() >= $this->criteria_value;

            case 'questions_answered':
                return $user->questionAnswers()->count() >= $this->criteria_value;

            default:
                return false;
        }
    }
}
