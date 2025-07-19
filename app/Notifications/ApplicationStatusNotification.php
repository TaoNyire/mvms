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
        $mail = (new MailMessage)
            ->greeting("Hello {$notifiable->name},");

        switch ($this->application->status) {
            case 'accepted':
                $mail->subject("You have been accepted! Please confirm your participation")
                    ->line("Congratulations! Your application for the opportunity '{$this->application->opportunity->title}' has been accepted.")
                    ->line('You must confirm your participation within 2 days, or your spot may be given to someone else.')
                    ->action('Confirm Participation', url("/applications/{$this->application->id}/confirm"))
                    ->line('Thank you for volunteering!');
                break;

            case 'rejected':
                // Check if rejection was due to recruitment closure
                $opportunity = $this->application->opportunity;
                $isRecruitmentClosed = $opportunity->status === 'recruitment_closed';

                if ($isRecruitmentClosed) {
                    $mail->subject("Opportunity Recruitment Closed")
                        ->line("We regret to inform you that recruitment for the opportunity '{$this->application->opportunity->title}' has been closed as we have reached our volunteer capacity.")
                        ->line('Your application was not processed in time, but we encourage you to apply for other opportunities.')
                        ->action('Browse Other Opportunities', url('/opportunities'));
                } else {
                    $mail->subject("Your Application has been Rejected")
                        ->line("We regret to inform you that your application for the opportunity '{$this->application->opportunity->title}' was not successful at this time.")
                        ->line('Thank you for your interest. Please feel free to apply for other opportunities.')
                        ->action('Browse Other Opportunities', url('/opportunities'));
                }
                break;

            case 'pending':
                $mail->subject("Application Received")
                    ->line("We have received your application for the opportunity '{$this->application->opportunity->title}'.")
                    ->line('You will be notified once your application has been reviewed.');
                break;

            default:
                $mail->subject("Your Application Status Updated")
                    ->line("The status of your application for the opportunity '{$this->application->opportunity->title}' is now: {$status}.");
                break;
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'opportunity_id' => $this->application->opportunity_id,
            'opportunity_title' => $this->application->opportunity->title,
            'status' => $this->application->status,
            'applied_at' => $this->application->applied_at,
        ];
    }
}