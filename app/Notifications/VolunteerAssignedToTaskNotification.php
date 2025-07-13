<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class VolunteerAssignedToTaskNotification extends Notification
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
                    ->subject("New Task Assignment: {$this->task->title}")
                    ->line("You have been assigned to a new task: '{$this->task->title}'")
                    ->line("Opportunity: {$this->task->opportunity->title}")
                    ->line("Organization: {$this->task->opportunity->organization->name}")
                    ->line("Task Description: {$this->task->description}")
                    ->line("Start Date: {$this->task->start_date->format('M d, Y')}")
                    ->line("End Date: {$this->task->end_date->format('M d, Y')}")
                    ->action('View Task Details', url("/volunteer/tasks"))
                    ->line('Thank you for your commitment to volunteering!');
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
            'start_date' => $this->task->start_date,
            'end_date' => $this->task->end_date,
        ];
    }
}
