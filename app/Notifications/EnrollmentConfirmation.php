<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class EnrollmentConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    private bool $mailOnly = false;

    public function __construct(public Course $course, public ?int $enrollmentId = null) {}

    public function via(object $notifiable): array
    {
        return $this->mailOnly ? ['mail'] : ['mail', 'database'];
    }

    public function onlyMail(): static
    {
        $this->mailOnly = true;

        return $this;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Enrolled: ' . $this->course->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been enrolled in the course: **' . $this->course->title . '**.')
            ->action('Start Learning', $this->actionUrl($notifiable))
            ->line('Happy learning!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'enrollment',
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'message' => 'You have been enrolled in <strong>' . $this->course->title . '</strong>.',
            'action_url' => $this->actionUrl($notifiable),
        ];
    }

    private function actionUrl(object $notifiable): string
    {
        $enrollmentId = $this->enrollmentId
            ?? $notifiable->enrollments()
                ->where('course_id', $this->course->id)
                ->value('id');

        $path = $enrollmentId
            ? route('student.enrollments.show', ['enrollment' => $enrollmentId], false)
            : route('student.courses.show', ['course' => $this->course->getRouteKey()], false);

        $tenantDomain = tenancy()->initialized
            ? tenant()->domains()->value('domain')
            : null;

        if (! $tenantDomain) {
            return URL::to($path);
        }

        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme . '://' . $tenantDomain . '/' . ltrim($path, '/');
    }
}
