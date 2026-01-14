<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewCourseSubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $course;

    /**
     * Create a new notification instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'course_submission',
            'course_id' => $this->course->id,
            'title' => $this->course->title,
            'instructor' => $this->course->instructor->name,
            'message' => "New course submitted: {$this->course->title}",
            'action_url' => "/admin/courses?status=pending", // direct link to pending courses
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'course_submission',
            'course_id' => $this->course->id,
            'title' => $this->course->title,
            'instructor' => $this->course->instructor->name,
            'message' => "New course submitted: {$this->course->title}",
            'action_url' => "/admin/courses?status=pending",
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
