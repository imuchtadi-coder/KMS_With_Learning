<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CourseCompleted extends Notification implements ShouldQueue
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
            ->subject('Course Completed: ' . $this->course->title)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('You have successfully completed the course **' . $this->course->title . '**.')
            ->action('View Certificate', route('student.certificates.view', $notifiable->certificates()->where('course_id', $this->course->id)->first()))
            ->line('Great achievement!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'course_completed',
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'message' => 'Congratulations! You completed <strong>' . $this->course->title . '</strong>.',
            'action_url' => route('student.certificates.view', $notifiable->certificates()->where('course_id', $this->course->id)->first()),
        ];
    }
}
