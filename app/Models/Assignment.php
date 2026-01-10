<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecture_id',
        'title',
        'description',
        'instructions',
        'max_score',
        'due_days', // days after enrollment
        'allow_late_submission',
        'attachment_allowed',
    ];

    protected $casts = [
        'max_score' => 'integer',
        'due_days' => 'integer',
        'allow_late_submission' => 'boolean',
        'attachment_allowed' => 'boolean',
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
