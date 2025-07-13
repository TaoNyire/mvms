<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskCompletedNotification extends Notification
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->greeting("Hello {$notifiable->name},")
                    ->subject("Task Completed: {$this->task->title}")
                    ->line("The task '{$this->task->title}' has been completed.")
                    ->line("Opportunity: {$this->task->opportunity->title}")
                    ->line("Organization: {$this->task->opportunity->organization->name}")
                    ->line('Thank you for your valuable contribution!')
                    ->action('View Opportunities', url('/opportunities'))
                    ->line('We look forward to working with you again.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'opportunity_id' => $this->task->opportunity_id,
            'opportunity_title' => $this->task->opportunity->title,
            'organization_name' => $this->task->opportunity->organization->name ?? 'Unknown',
            'completed_at' => $this->task->completed_at,
        ];
    }
}
