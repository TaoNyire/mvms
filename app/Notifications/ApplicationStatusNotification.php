<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Application;

class ApplicationStatusNotification extends Notification
{
    use Queueable;

    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->application->status);
        return (new MailMessage)
            ->subject("Your Application has been {$status}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your application for the opportunity '{$this->application->opportunity->title}' has been {$status}.")
            ->action('View Opportunity', url("/opportunities/{$this->application->opportunity_id}"))
            ->line('Thank you for volunteering!');
    }

    public function toArray($notifiable)
    {
        return [
            'opportunity_id' => $this->application->opportunity_id,
            'opportunity_title' => $this->application->opportunity->title,
            'status' => $this->application->status,
            'responded_at' => $this->application->responded_at,
        ];
    }
}