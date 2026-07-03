<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AssignmentGraded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AssignmentSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assignment Graded: ' . $this->submission->assignment->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your assignment **' . $this->submission->assignment->title . '** has been graded.')
            ->line('Score: ' . ($this->submission->grade ?? 'N/A'))
            ->action('View Submission', route('student.assignments.show', $this->submission->assignment))
            ->line('Keep up the good work!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'assignment_graded',
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment->title,
            'grade' => $this->submission->grade,
            'message' => 'Your assignment <strong>' . $this->submission->assignment->title . '</strong> has been graded.',
            'action_url' => route('student.assignments.show', $this->submission->assignment),
        ];
    }
}
