<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecture_id',
        'title',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'downloads',
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }

    /**
     * Get file size in human-readable format
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
