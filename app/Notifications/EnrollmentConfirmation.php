<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EnrollmentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Course $course) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrolled: ' . $this->course->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been enrolled in the course: **' . $this->course->title . '**.')
            ->action('Start Learning', route('student.enrollments.show', $notifiable->enrollments()->where('course_id', $this->course->id)->first()))
            ->line('Happy learning!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'enrollment',
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'message' => 'You have been enrolled in <strong>' . $this->course->title . '</strong>.',
            'action_url' => route('student.enrollments.show', $notifiable->enrollments()->where('course_id', $this->course->id)->first()),
        ];
    }
}
