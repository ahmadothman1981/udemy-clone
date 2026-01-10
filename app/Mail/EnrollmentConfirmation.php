<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Course $course;

    public function __construct(User $user, Course $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re enrolled in ' . $this->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
