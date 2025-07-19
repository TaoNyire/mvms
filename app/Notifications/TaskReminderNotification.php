<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $task = $this->assignment->task;
        $opportunity = $task->opportunity;
        $hoursUntilStart = now()->diffInHours($this->assignment->scheduled_start);

        return (new MailMessage)
            ->subject('Task Reminder - ' . $task->title . ' starts in ' . $hoursUntilStart . ' hours')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder about your upcoming volunteer task.')
            ->line('**Task Details:**')
            ->line('• **Task:** ' . $task->title)
            ->line('• **Opportunity:** ' . $opportunity->title)
            ->line('• **Start Time:** ' . $this->assignment->scheduled_start->format('M j, Y \a\t g:i A'))
            ->line('• **Duration:** ' . $task->duration_hours . ' hours')
            ->line('• **Location:** ' . ($task->location_type === 'remote' ? 'Remote' : $task->location_address))
            ->when($task->location_instructions, function($message) use ($task) {
                return $message->line('• **Location Instructions:** ' . $task->location_instructions);
            })
            ->when($task->equipment_needed, function($message) use ($task) {
                return $message->line('• **Bring:** ' . implode(', ', $task->equipment_needed));
            })
            ->when($task->safety_requirements, function($message) use ($task) {
                return $message->line('• **Safety Requirements:** ' . $task->safety_requirements);
            })
            ->line('Please arrive on time and prepared. If you need to cancel or have any issues, please contact the organization immediately.')
            ->action('View Task Details', route('volunteer.tasks.show', $this->assignment))
            ->line('Thank you for your commitment to volunteering!');
    }

    public function toArray($notifiable)
    {
        $task = $this->assignment->task;
        $opportunity = $task->opportunity;
        $hoursUntilStart = now()->diffInHours($this->assignment->scheduled_start);

        return [
            'type' => 'task_reminder',
            'assignment_id' => $this->assignment->id,
            'task_id' => $task->id,
            'task_title' => $task->title,
            'opportunity_title' => $opportunity->title,
            'scheduled_start' => $this->assignment->scheduled_start,
            'hours_until_start' => $hoursUntilStart,
            'location_type' => $task->location_type,
            'location_address' => $task->location_address,
            'message' => "Reminder: Your task '{$task->title}' starts in {$hoursUntilStart} hours",
            'action_url' => route('volunteer.tasks.show', $this->assignment),
            'action_text' => 'View Task'
        ];
    }
}
