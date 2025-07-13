<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Application;

class NewApplicationNotification extends Notification
{
    use Queueable;

    protected $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
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
        $volunteer = $this->application->volunteer;
        $opportunity = $this->application->opportunity;

        return (new MailMessage)
                    ->greeting("Hello {$notifiable->name},")
                    ->subject("New Volunteer Application: {$opportunity->title}")
                    ->line("You have received a new volunteer application for your opportunity '{$opportunity->title}'.")
                    ->line("**Volunteer Details:**")
                    ->line("Name: {$volunteer->name}")
                    ->line("Email: {$volunteer->email}")
                    ->line("Applied on: {$this->application->applied_at->format('M d, Y \a\t H:i')}")
                    ->line("**Opportunity Details:**")
                    ->line("Title: {$opportunity->title}")
                    ->line("Location: {$opportunity->location}")
                    ->line("Start Date: {$opportunity->start_date->format('M d, Y')}")
                    ->action('Review Application', url("/organization/applications/{$this->application->id}"))
                    ->line('Please review the application and respond as soon as possible.')
                    ->line('Thank you for using our volunteer management system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'volunteer_id' => $this->application->volunteer_id,
            'volunteer_name' => $this->application->volunteer->name,
            'volunteer_email' => $this->application->volunteer->email,
            'opportunity_id' => $this->application->opportunity_id,
            'opportunity_title' => $this->application->opportunity->title,
            'applied_at' => $this->application->applied_at,
            'type' => 'new_application'
        ];
    }
}
