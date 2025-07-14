<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notifications_enabled',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'in_app_task_assigned',
        'in_app_task_updated',
        'in_app_application_status',
        'in_app_messages',
        'in_app_announcements',
        'in_app_reminders',
        'in_app_schedule_changes',
        'email_task_assigned',
        'email_task_updated',
        'email_application_status',
        'email_messages',
        'email_announcements',
        'email_reminders',
        'email_schedule_changes',
        'email_weekly_digest',
        'sms_urgent_only',
        'sms_task_assigned',
        'sms_application_status',
        'sms_reminders',
        'sms_schedule_changes',
        'quiet_hours_start',
        'quiet_hours_end',
        'respect_quiet_hours',
        'notification_days',
        'digest_frequency',
        'reminder_frequency',
        'max_notifications_per_day',
        'group_similar_notifications',
        'notification_retention_days',
        'auto_mark_read_after_days',
        'auto_mark_read_days',
        'preferred_email',
        'preferred_phone',
        'timezone',
        'language',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'in_app_task_assigned' => 'boolean',
        'in_app_task_updated' => 'boolean',
        'in_app_application_status' => 'boolean',
        'in_app_messages' => 'boolean',
        'in_app_announcements' => 'boolean',
        'in_app_reminders' => 'boolean',
        'in_app_schedule_changes' => 'boolean',
        'email_task_assigned' => 'boolean',
        'email_task_updated' => 'boolean',
        'email_application_status' => 'boolean',
        'email_messages' => 'boolean',
        'email_announcements' => 'boolean',
        'email_reminders' => 'boolean',
        'email_schedule_changes' => 'boolean',
        'email_weekly_digest' => 'boolean',
        'sms_urgent_only' => 'boolean',
        'sms_task_assigned' => 'boolean',
        'sms_application_status' => 'boolean',
        'sms_reminders' => 'boolean',
        'sms_schedule_changes' => 'boolean',
        'respect_quiet_hours' => 'boolean',
        'notification_days' => 'array',
        'group_similar_notifications' => 'boolean',
        'auto_mark_read_after_days' => 'boolean',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
    ];

    /**
     * Relationship: Preferences belong to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default notification preferences
     */
    public static function getDefaults(): array
    {
        return [
            'notifications_enabled' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'in_app_task_assigned' => true,
            'in_app_task_updated' => true,
            'in_app_application_status' => true,
            'in_app_messages' => true,
            'in_app_announcements' => true,
            'in_app_reminders' => true,
            'in_app_schedule_changes' => true,
            'email_task_assigned' => true,
            'email_task_updated' => false,
            'email_application_status' => true,
            'email_messages' => false,
            'email_announcements' => true,
            'email_reminders' => true,
            'email_schedule_changes' => true,
            'email_weekly_digest' => true,
            'sms_urgent_only' => true,
            'sms_task_assigned' => false,
            'sms_application_status' => false,
            'sms_reminders' => false,
            'sms_schedule_changes' => false,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
            'respect_quiet_hours' => true,
            'notification_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'digest_frequency' => 'weekly',
            'reminder_frequency' => 'once',
            'max_notifications_per_day' => 50,
            'group_similar_notifications' => true,
            'notification_retention_days' => 30,
            'auto_mark_read_after_days' => false,
            'auto_mark_read_days' => 7,
            'timezone' => 'Africa/Blantyre',
            'language' => 'en',
        ];
    }

    /**
     * Create default preferences for user
     */
    public static function createForUser(User $user): self
    {
        return static::create(array_merge(
            ['user_id' => $user->id],
            static::getDefaults()
        ));
    }

    /**
     * Check if should send email for notification type
     */
    public function shouldSendEmail(string $type): bool
    {
        if (!$this->email_notifications) {
            return false;
        }

        if ($this->isInQuietHours()) {
            return false;
        }

        return match($type) {
            'task_assigned' => $this->email_task_assigned,
            'task_updated' => $this->email_task_updated,
            'application_status' => $this->email_application_status,
            'message_received' => $this->email_messages,
            'announcement' => $this->email_announcements,
            'reminder' => $this->email_reminders,
            'schedule_change' => $this->email_schedule_changes,
            default => false
        };
    }

    /**
     * Check if should send SMS for notification type
     */
    public function shouldSendSms(string $type): bool
    {
        if (!$this->sms_notifications) {
            return false;
        }

        if ($this->isInQuietHours()) {
            return false;
        }

        // If SMS urgent only is enabled, only send for urgent notifications
        if ($this->sms_urgent_only) {
            return false; // Would need to check notification priority
        }

        return match($type) {
            'task_assigned' => $this->sms_task_assigned,
            'application_status' => $this->sms_application_status,
            'reminder' => $this->sms_reminders,
            'schedule_change' => $this->sms_schedule_changes,
            default => false
        };
    }

    /**
     * Check if should send push notification for type
     */
    public function shouldSendPush(string $type): bool
    {
        // For now, push notifications are enabled for all types if push is enabled
        // In the future, we could add type-specific push preferences
        return $this->push_notifications && !$this->isInQuietHours();
    }

    /**
     * Check if current time is in quiet hours
     */
    public function isInQuietHours(): bool
    {
        if (!$this->respect_quiet_hours) {
            return false;
        }

        $now = now($this->timezone);
        $currentTime = $now->format('H:i');

        $start = $this->quiet_hours_start;
        $end = $this->quiet_hours_end;

        // Handle overnight quiet hours (e.g., 22:00 to 08:00)
        if ($start > $end) {
            return $currentTime >= $start || $currentTime <= $end;
        }

        // Handle same-day quiet hours (e.g., 12:00 to 14:00)
        return $currentTime >= $start && $currentTime <= $end;
    }

    /**
     * Check if notifications are allowed today
     */
    public function isNotificationDayToday(): bool
    {
        if (!$this->notification_days) {
            return true;
        }

        $today = strtolower(now($this->timezone)->format('l'));
        return in_array($today, $this->notification_days);
    }

    /**
     * Get user's preferred email
     */
    public function getPreferredEmail(): string
    {
        return $this->preferred_email ?: $this->user->email;
    }

    /**
     * Get user's preferred phone
     */
    public function getPreferredPhone(): ?string
    {
        return $this->preferred_phone ?: $this->user->phone;
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(array $preferences): bool
    {
        return $this->update($preferences);
    }

    /**
     * Reset to default preferences
     */
    public function resetToDefaults(): bool
    {
        return $this->update(static::getDefaults());
    }
}
