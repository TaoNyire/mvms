<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'status',
        'priority',
        'read_at',
        'archived_at',
        'related_user_id',
        'related_type',
        'related_id',
        'sent_in_app',
        'sent_email',
        'sent_sms',
        'sent_push',
        'email_sent_at',
        'sms_sent_at',
        'push_sent_at',
        'email_failed',
        'sms_failed',
        'push_failed',
        'action_url',
        'action_text',
        'action_data',
        'icon',
        'color',
        'expires_at',
        'is_system',
    ];

    protected $casts = [
        'data' => 'array',
        'action_data' => 'array',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'push_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'sent_in_app' => 'boolean',
        'sent_email' => 'boolean',
        'sent_sms' => 'boolean',
        'sent_push' => 'boolean',
        'email_failed' => 'boolean',
        'sms_failed' => 'boolean',
        'push_failed' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected $appends = [
        'is_read',
        'is_archived',
        'is_expired',
        'time_ago',
        'priority_label',
    ];

    /**
     * Relationship: Notification belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Notification belongs to a Related User (optional)
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related()
    {
        if ($this->related_type && $this->related_id) {
            $modelClass = 'App\\Models\\' . ucfirst($this->related_type);
            if (class_exists($modelClass)) {
                return $modelClass::find($this->related_id);
            }
        }
        return null;
    }

    /**
     * Check if notification is read
     */
    public function getIsReadAttribute(): bool
    {
        return $this->status === 'read' || $this->read_at !== null;
    }

    /**
     * Check if notification is archived
     */
    public function getIsArchivedAttribute(): bool
    {
        return $this->status === 'archived' || $this->archived_at !== null;
    }

    /**
     * Check if notification is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Get human-readable time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'Urgent',
            'high' => 'High Priority',
            'medium' => 'Medium Priority',
            'low' => 'Low Priority',
            default => ucfirst($this->priority)
        };
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope: Read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope: Archived notifications
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Not expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);

        return $this;
    }

    /**
     * Archive notification
     */
    public function archive()
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        return $this;
    }

    /**
     * Unarchive notification
     */
    public function unarchive()
    {
        $this->update([
            'status' => $this->read_at ? 'read' : 'unread',
            'archived_at' => null,
        ]);

        return $this;
    }

    /**
     * Get notification icon based on type
     */
    public function getDefaultIcon(): string
    {
        return match($this->type) {
            'task_assigned' => 'bi-person-check',
            'task_updated' => 'bi-pencil-square',
            'application_status' => 'bi-file-earmark-check',
            'message_received' => 'bi-chat-dots',
            'announcement' => 'bi-megaphone',
            'reminder' => 'bi-bell',
            'schedule_change' => 'bi-calendar-event',
            'system' => 'bi-gear',
            default => 'bi-info-circle'
        };
    }

    /**
     * Get notification color based on priority and type
     */
    public function getDefaultColor(): string
    {
        if ($this->priority === 'urgent') return '#dc3545';
        if ($this->priority === 'high') return '#fd7e14';

        return match($this->type) {
            'task_assigned' => '#28a745',
            'application_status' => '#007bff',
            'message_received' => '#17a2b8',
            'announcement' => '#6f42c1',
            'reminder' => '#ffc107',
            'schedule_change' => '#fd7e14',
            default => '#6c757d'
        };
    }

    /**
     * Create notification for user
     */
    public static function createForUser(User $user, array $data)
    {
        $notification = static::create(array_merge([
            'user_id' => $user->id,
            'icon' => null,
            'color' => null,
        ], $data));

        // Set default icon and color if not provided
        if (!$notification->icon) {
            $notification->update(['icon' => $notification->getDefaultIcon()]);
        }
        if (!$notification->color) {
            $notification->update(['color' => $notification->getDefaultColor()]);
        }

        return $notification;
    }

    /**
     * Send notification through various channels
     */
    public function send()
    {
        $preferences = $this->user->notificationPreferences;

        if (!$preferences || !$preferences->notifications_enabled) {
            return false;
        }

        // Send in-app notification (always sent)
        $this->update(['sent_in_app' => true]);

        // Send email notification
        if ($preferences->shouldSendEmail($this->type)) {
            $this->sendEmail();
        }

        // Send SMS notification
        if ($preferences->shouldSendSms($this->type)) {
            $this->sendSms();
        }

        // Send push notification
        if ($preferences->shouldSendPush($this->type)) {
            $this->sendPush();
        }

        return true;
    }

    /**
     * Send email notification
     */
    protected function sendEmail()
    {
        try {
            // Email sending logic would go here
            // For now, just mark as sent
            $this->update([
                'sent_email' => true,
                'email_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->update(['email_failed' => true]);
            Log::error("Failed to send email notification {$this->id}: " . $e->getMessage());
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSms()
    {
        try {
            // SMS sending logic would go here
            // For now, just mark as sent
            $this->update([
                'sent_sms' => true,
                'sms_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->update(['sms_failed' => true]);
            Log::error("Failed to send SMS notification {$this->id}: " . $e->getMessage());
        }
    }

    /**
     * Send push notification
     */
    protected function sendPush()
    {
        try {
            // Push notification logic would go here
            // For now, just mark as sent
            $this->update([
                'sent_push' => true,
                'push_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->update(['push_failed' => true]);
            Log::error("Failed to send push notification {$this->id}: " . $e->getMessage());
        }
    }
}
