<?php

namespace App\Listeners;

use App\Events\CourseSubmitted;
use App\Models\User;
use App\Notifications\NewCourseSubmissionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendNewCourseSubmissionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CourseSubmitted $event): void
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'superadmin']);
        })->get();

        Notification::send($admins, new NewCourseSubmissionNotification($event->course));
    }
}
