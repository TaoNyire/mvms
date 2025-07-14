<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Task;
use App\Models\Assignment;
use App\Models\Application;
use App\Models\Opportunity;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create and send notification for task assignment
     */
    public function notifyTaskAssigned(Assignment $assignment): Notification
    {
        $volunteer = $assignment->volunteer;
        $task = $assignment->task;
        $opportunity = $task->opportunity;
        
        return $this->createNotification($volunteer, [
            'type' => 'task_assigned',
            'title' => 'New Task Assignment',
            'message' => "You have been assigned to the task '{$task->title}' for the opportunity '{$opportunity->title}'.",
            'priority' => $task->priority === 'urgent' ? 'urgent' : 'medium',
            'related_type' => 'assignment',
            'related_id' => $assignment->id,
            'related_user_id' => $assignment->assigned_by,
            'action_url' => route('assignments.show', $assignment),
            'action_text' => 'View Assignment',
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'opportunity_id' => $opportunity->id,
                'opportunity_title' => $opportunity->title,
                'start_date' => $assignment->scheduled_start->toDateString(),
                'start_time' => $assignment->scheduled_start->format('H:i'),
            ]
        ]);
    }

    /**
     * Create and send notification for task update
     */
    public function notifyTaskUpdated(Task $task, array $changes): void
    {
        $assignments = $task->assignments()->whereIn('status', ['pending', 'accepted'])->with('volunteer')->get();
        
        foreach ($assignments as $assignment) {
            $this->createNotification($assignment->volunteer, [
                'type' => 'task_updated',
                'title' => 'Task Updated',
                'message' => "The task '{$task->title}' has been updated. Please review the changes.",
                'priority' => 'medium',
                'related_type' => 'task',
                'related_id' => $task->id,
                'related_user_id' => $task->created_by,
                'action_url' => route('assignments.show', $assignment),
                'action_text' => 'View Task',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'changes' => $changes,
                ]
            ]);
        }
    }

    /**
     * Create and send notification for application status change
     */
    public function notifyApplicationStatus(Application $application): Notification
    {
        $volunteer = $application->volunteer;
        $opportunity = $application->opportunity;
        
        $statusMessages = [
            'accepted' => "Congratulations! Your application for '{$opportunity->title}' has been accepted.",
            'rejected' => "Thank you for your interest. Your application for '{$opportunity->title}' was not selected this time.",
            'withdrawn' => "Your application for '{$opportunity->title}' has been withdrawn.",
        ];
        
        $priorities = [
            'accepted' => 'high',
            'rejected' => 'medium',
            'withdrawn' => 'low',
        ];
        
        return $this->createNotification($volunteer, [
            'type' => 'application_status',
            'title' => 'Application Status Update',
            'message' => $statusMessages[$application->status] ?? "Your application status has been updated.",
            'priority' => $priorities[$application->status] ?? 'medium',
            'related_type' => 'application',
            'related_id' => $application->id,
            'related_user_id' => $application->opportunity->organization_id,
            'action_url' => route('volunteer.opportunities.show', $opportunity),
            'action_text' => 'View Opportunity',
            'data' => [
                'application_id' => $application->id,
                'opportunity_id' => $opportunity->id,
                'opportunity_title' => $opportunity->title,
                'status' => $application->status,
                'reason' => $application->rejection_reason,
            ]
        ]);
    }

    /**
     * Create and send notification for schedule change
     */
    public function notifyScheduleChange(Assignment $assignment, array $changes): Notification
    {
        $volunteer = $assignment->volunteer;
        $task = $assignment->task;
        
        return $this->createNotification($volunteer, [
            'type' => 'schedule_change',
            'title' => 'Schedule Change',
            'message' => "The schedule for your task '{$task->title}' has been changed. Please review the new details.",
            'priority' => 'high',
            'related_type' => 'assignment',
            'related_id' => $assignment->id,
            'related_user_id' => $assignment->assigned_by,
            'action_url' => route('assignments.show', $assignment),
            'action_text' => 'View Assignment',
            'data' => [
                'assignment_id' => $assignment->id,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'changes' => $changes,
                'new_start' => $assignment->scheduled_start->toDateTimeString(),
                'new_end' => $assignment->scheduled_end->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Create and send reminder notification
     */
    public function notifyReminder(Assignment $assignment, string $reminderType = 'upcoming'): Notification
    {
        $volunteer = $assignment->volunteer;
        $task = $assignment->task;
        
        $messages = [
            'upcoming' => "Reminder: You have an upcoming task '{$task->title}' starting soon.",
            'overdue' => "Your task '{$task->title}' is overdue. Please check in or contact the organization.",
            'check_in' => "Don't forget to check in for your task '{$task->title}'.",
        ];
        
        return $this->createNotification($volunteer, [
            'type' => 'reminder',
            'title' => 'Task Reminder',
            'message' => $messages[$reminderType] ?? $messages['upcoming'],
            'priority' => $reminderType === 'overdue' ? 'urgent' : 'medium',
            'related_type' => 'assignment',
            'related_id' => $assignment->id,
            'action_url' => route('assignments.show', $assignment),
            'action_text' => 'View Assignment',
            'data' => [
                'assignment_id' => $assignment->id,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'reminder_type' => $reminderType,
                'start_time' => $assignment->scheduled_start->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Create and send notification for new message
     */
    public function notifyNewMessage(User $recipient, User $sender, string $messageContent, $relatedModel = null): Notification
    {
        return $this->createNotification($recipient, [
            'type' => 'message_received',
            'title' => 'New Message',
            'message' => "You have a new message from {$sender->name}: " . \Str::limit($messageContent, 100),
            'priority' => 'medium',
            'related_type' => $relatedModel ? class_basename($relatedModel) : null,
            'related_id' => $relatedModel ? $relatedModel->id : null,
            'related_user_id' => $sender->id,
            'action_url' => route('messages.index'),
            'action_text' => 'View Messages',
            'data' => [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'message_preview' => \Str::limit($messageContent, 200),
            ]
        ]);
    }

    /**
     * Create and send system notification
     */
    public function notifySystem(User $user, string $title, string $message, array $data = []): Notification
    {
        return $this->createNotification($user, [
            'type' => 'system',
            'title' => $title,
            'message' => $message,
            'priority' => 'medium',
            'is_system' => true,
            'data' => $data,
        ]);
    }

    /**
     * Send bulk notifications to multiple users
     */
    public function notifyBulk(array $users, array $notificationData): array
    {
        $notifications = [];
        
        foreach ($users as $user) {
            try {
                $notifications[] = $this->createNotification($user, $notificationData);
            } catch (\Exception $e) {
                Log::error("Failed to create notification for user {$user->id}: " . $e->getMessage());
            }
        }
        
        return $notifications;
    }

    /**
     * Create notification and send through appropriate channels
     */
    protected function createNotification(User $user, array $data): Notification
    {
        try {
            $notification = Notification::createForUser($user, $data);
            
            // Send through appropriate channels based on user preferences
            $notification->send();
            
            return $notification;
        } catch (\Exception $e) {
            Log::error("Failed to create notification for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up old notifications based on user preferences
     */
    public function cleanupOldNotifications(): int
    {
        $deletedCount = 0;
        
        // Get all users with notification preferences
        $users = User::whereHas('notificationPreferences')->with('notificationPreferences')->get();
        
        foreach ($users as $user) {
            $preferences = $user->notificationPreferences;
            $retentionDays = $preferences->notification_retention_days ?? 30;
            
            $deleted = Notification::where('user_id', $user->id)
                ->where('created_at', '<', now()->subDays($retentionDays))
                ->delete();
            
            $deletedCount += $deleted;
            
            // Auto-mark as read if enabled
            if ($preferences->auto_mark_read_after_days) {
                Notification::where('user_id', $user->id)
                    ->where('status', 'unread')
                    ->where('created_at', '<', now()->subDays($preferences->auto_mark_read_days))
                    ->update([
                        'status' => 'read',
                        'read_at' => now()
                    ]);
            }
        }
        
        return $deletedCount;
    }

    /**
     * Get notification statistics for user
     */
    public function getNotificationStats(User $user): array
    {
        return [
            'total' => Notification::where('user_id', $user->id)->count(),
            'unread' => Notification::where('user_id', $user->id)->unread()->count(),
            'read' => Notification::where('user_id', $user->id)->read()->count(),
            'archived' => Notification::where('user_id', $user->id)->archived()->count(),
            'today' => Notification::where('user_id', $user->id)->whereDate('created_at', today())->count(),
            'this_week' => Notification::where('user_id', $user->id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }
}
