<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'content',
        'attachment_path',
        'score',
        'feedback',
        'graded_at',
        'graded_by',
        'submitted_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'graded_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Check if submission is graded
     */
    public function isGraded(): bool
    {
        return !is_null($this->graded_at);
    }

    /**
     * Get score percentage
     */
    public function getScorePercentageAttribute(): float
    {
        if (!$this->score || !$this->assignment->max_score)
            return 0;
        return round(($this->score / $this->assignment->max_score) * 100, 1);
    }
}
