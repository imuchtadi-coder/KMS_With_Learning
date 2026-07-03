<?php

namespace App\Notifications;

use App\Models\QuizAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QuizGraded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public QuizAttempt $attempt) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quiz Result: ' . $this->attempt->quiz->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your quiz **' . $this->attempt->quiz->title . '** has been graded.')
            ->line('Score: ' . $this->attempt->score . '%')
            ->action('View Result', route('student.quizzes.result', $this->attempt))
            ->line('Keep up the good work!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'quiz_graded',
            'quiz_id' => $this->attempt->quiz_id,
            'quiz_title' => $this->attempt->quiz->title,
            'score' => $this->attempt->score,
            'message' => 'Your quiz <strong>' . $this->attempt->quiz->title . '</strong> scored <strong>' . $this->attempt->score . '%</strong>.',
            'action_url' => route('student.quizzes.result', $this->attempt),
        ];
    }
}
