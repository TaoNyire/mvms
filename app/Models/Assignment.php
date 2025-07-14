<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'volunteer_id',
        'assigned_by',
        'status',
        'assignment_method',
        'assignment_notes',
        'volunteer_notes',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'break_minutes',
        'assigned_at',
        'responded_at',
        'accepted_at',
        'declined_at',
        'completed_at',
        'decline_reason',
        'checked_in_at',
        'checked_out_at',
        'check_in_location',
        'check_out_location',
        'check_in_notes',
        'check_out_notes',
        'performance_rating',
        'performance_notes',
        'volunteer_feedback',
        'task_completed_successfully',
        'notification_sent',
        'last_notification_sent',
        'notification_count',
        'reminder_sent',
        'has_conflict',
        'conflict_details',
        'conflict_status',
        'conflict_resolved_at',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'assigned_at' => 'datetime',
        'responded_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'completed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'last_notification_sent' => 'datetime',
        'conflict_resolved_at' => 'datetime',
        'notification_sent' => 'boolean',
        'reminder_sent' => 'boolean',
        'has_conflict' => 'boolean',
        'task_completed_successfully' => 'boolean',
    ];

    protected $appends = [
        'status_label',
        'duration_hours',
        'is_overdue',
        'can_check_in',
        'can_check_out',
        'response_time_hours',
    ];

    /**
     * Relationship: Assignment belongs to a Task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship: Assignment belongs to a Volunteer (User)
     */
    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    /**
     * Relationship: Assignment belongs to an Assigner (User)
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Response',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get duration in hours
     */
    public function getDurationHoursAttribute(): float
    {
        if ($this->actual_start && $this->actual_end) {
            $minutes = $this->actual_start->diffInMinutes($this->actual_end);
            return round(($minutes - $this->break_minutes) / 60, 2);
        }

        if ($this->scheduled_start && $this->scheduled_end) {
            return round($this->scheduled_start->diffInMinutes($this->scheduled_end) / 60, 2);
        }

        return 0;
    }

    /**
     * Check if assignment is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' &&
               $this->scheduled_start->isPast();
    }

    /**
     * Check if volunteer can check in
     */
    public function getCanCheckInAttribute(): bool
    {
        return $this->status === 'accepted' &&
               !$this->checked_in_at &&
               $this->scheduled_start->subHours(2)->isPast() &&
               $this->scheduled_start->addHours(1)->isFuture();
    }

    /**
     * Check if volunteer can check out
     */
    public function getCanCheckOutAttribute(): bool
    {
        return $this->status === 'accepted' &&
               $this->checked_in_at &&
               !$this->checked_out_at;
    }

    /**
     * Get response time in hours
     */
    public function getResponseTimeHoursAttribute(): ?float
    {
        if ($this->responded_at) {
            return round($this->assigned_at->diffInHours($this->responded_at), 1);
        }
        return null;
    }

    /**
     * Scope: Pending assignments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Accepted assignments
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: Assignments in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_start', [$startDate, $endDate]);
    }

    /**
     * Scope: Assignments with conflicts
     */
    public function scopeWithConflicts($query)
    {
        return $query->where('has_conflict', true)
                    ->where('conflict_status', '!=', 'resolved');
    }

    /**
     * Accept the assignment
     */
    public function accept($notes = null)
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'responded_at' => now(),
            'volunteer_notes' => $notes,
        ]);

        // Send confirmation notification
        $this->sendNotification('accepted');

        return $this;
    }

    /**
     * Decline the assignment
     */
    public function decline($reason = null)
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
            'responded_at' => now(),
            'decline_reason' => $reason,
        ]);

        // Update task volunteers count
        $this->task->decrement('volunteers_assigned');

        // Send notification
        $this->sendNotification('declined');

        return $this;
    }

    /**
     * Check in volunteer
     */
    public function checkIn($location = null, $notes = null)
    {
        if (!$this->can_check_in) {
            throw new \Exception('Cannot check in at this time.');
        }

        $this->update([
            'checked_in_at' => now(),
            'actual_start' => now(),
            'check_in_location' => $location,
            'check_in_notes' => $notes,
        ]);

        return $this;
    }

    /**
     * Check out volunteer
     */
    public function checkOut($location = null, $notes = null, $taskCompleted = null)
    {
        if (!$this->can_check_out) {
            throw new \Exception('Cannot check out - not checked in.');
        }

        $this->update([
            'checked_out_at' => now(),
            'actual_end' => now(),
            'check_out_location' => $location,
            'check_out_notes' => $notes,
            'task_completed_successfully' => $taskCompleted,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Resolve scheduling conflict
     */
    public function resolveConflict($resolution = 'resolved')
    {
        $this->update([
            'conflict_status' => $resolution,
            'conflict_resolved_at' => now(),
        ]);

        return $this;
    }

    /**
     * Send notification to volunteer
     */
    public function sendNotification($type)
    {
        try {
            // This would integrate with your notification system
            \Log::info("Assignment notification sent to volunteer {$this->volunteer->email} - Type: {$type}");

            $this->update([
                'notification_sent' => true,
                'last_notification_sent' => now(),
                'notification_count' => $this->notification_count + 1,
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send assignment notification {$this->id}: " . $e->getMessage());
        }
    }

    /**
     * Send reminder notification
     */
    public function sendReminder()
    {
        if ($this->status === 'pending' && !$this->reminder_sent) {
            $this->sendNotification('reminder');
            $this->update(['reminder_sent' => true]);
        }
    }

    /**
     * Mark as no show
     */
    public function markAsNoShow($notes = null)
    {
        $this->update([
            'status' => 'no_show',
            'performance_notes' => $notes,
        ]);

        // Update task volunteers count
        $this->task->decrement('volunteers_assigned');

        return $this;
    }
}
