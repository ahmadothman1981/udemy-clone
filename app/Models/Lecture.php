<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'type',
        'content',
        'video_path',
        'video_url',
        'hls_path',
        'processing_state',
        'duration_minutes',
        'preview',
        'order',
        'free_preview',
    ];

    protected $casts = [
        'preview' => 'boolean',
        'free_preview' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function resources()
    {
        return $this->hasMany(LectureResource::class);
    }
}
