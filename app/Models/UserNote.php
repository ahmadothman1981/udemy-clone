<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'lecture_id',
        'content',
        'video_timestamp',
    ];

    protected $casts = [
        'video_timestamp' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }

    /**
     * Get formatted timestamp (e.g., "5:23")
     */
    public function getFormattedTimestampAttribute()
    {
        if (!$this->video_timestamp)
            return null;

        $minutes = floor($this->video_timestamp / 60);
        $seconds = $this->video_timestamp % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
