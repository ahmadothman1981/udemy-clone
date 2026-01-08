<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_question_id',
        'user_id',
        'answer',
        'best_answer',
    ];

    protected $casts = [
        'best_answer' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(CourseQuestion::class, 'course_question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
