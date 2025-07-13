<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ApplicationTaskStatus;

class TaskStatusUpdateNotification extends Notification
{
    use Queueable;

    protected $taskStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(ApplicationTaskStatus $taskStatus)
    {
        $this->taskStatus = $taskStatus;
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
        $application = $this->taskStatus->application;
        $task = $application->task;
        $opportunity = $application->opportunity;
        $status = ucfirst($this->taskStatus->status);

        $mail = (new MailMessage)
                    ->greeting("Hello {$notifiable->name},")
                    ->subject("Task Status Update: {$task->title}");

        switch ($this->taskStatus->status) {
            case 'in_progress':
                $mail->line("Your task '{$task->title}' has been marked as in progress.")
                     ->line("Opportunity: {$opportunity->title}")
                     ->line("Organization: {$opportunity->organization->name}")
                     ->line('Keep up the great work!');
                break;

            case 'completed':
                $mail->line("Congratulations! Your task '{$task->title}' has been marked as completed.")
                     ->line("Opportunity: {$opportunity->title}")
                     ->line("Organization: {$opportunity->organization->name}")
                     ->line('Thank you for your valuable contribution!');
                
                if ($this->taskStatus->completion_notes) {
                    $mail->line("Notes: {$this->taskStatus->completion_notes}");
                }
                break;

            case 'quit':
                $mail->line("Your task '{$task->title}' status has been updated to quit.")
                     ->line("Opportunity: {$opportunity->title}")
                     ->line("Organization: {$opportunity->organization->name}");
                
                if ($this->taskStatus->completion_notes) {
                    $mail->line("Notes: {$this->taskStatus->completion_notes}");
                }
                break;

            default:
                $mail->line("Your task '{$task->title}' status has been updated to: {$status}")
                     ->line("Opportunity: {$opportunity->title}")
                     ->line("Organization: {$opportunity->organization->name}");
        }

        return $mail->action('View Task Details', url("/volunteer/tasks"))
                    ->line('Thank you for volunteering with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $application = $this->taskStatus->application;
        $task = $application->task;
        $opportunity = $application->opportunity;

        return [
            'task_status_id' => $this->taskStatus->id,
            'application_id' => $application->id,
            'task_id' => $task->id,
            'task_title' => $task->title,
            'opportunity_id' => $opportunity->id,
            'opportunity_title' => $opportunity->title,
            'organization_name' => $opportunity->organization->name ?? 'Unknown',
            'status' => $this->taskStatus->status,
            'started_at' => $this->taskStatus->started_at,
            'completed_at' => $this->taskStatus->completed_at,
            'completion_notes' => $this->taskStatus->completion_notes,
        ];
    }
}
