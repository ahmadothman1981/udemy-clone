<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_number',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    /**
     * Generate a unique certificate number
     */
    public static function generateCertificateNumber()
    {
        do {
            $number = 'CERT-' . strtoupper(Str::random(8)) . '-' . date('Y');
        } while (self::where('certificate_number', $number)->exists());

        return $number;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
