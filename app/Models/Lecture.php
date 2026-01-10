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
        'video_url',
        'video_path',
        'duration_minutes',
        'order',
        'preview',
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
}
