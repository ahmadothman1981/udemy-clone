<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Course $course;
    public ?Certificate $certificate;

    public function __construct(User $user, Course $course, ?Certificate $certificate = null)
    {
        $this->user = $user;
        $this->course = $course;
        $this->certificate = $certificate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! You completed ' . $this->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.course-completed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
