<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'start_datetime',
        'end_datetime',
        'is_all_day',
        'timezone',
        'location',
        'location_coordinates',
        'location_type',
        'task_id',
        'assignment_id',
        'opportunity_id',
        'is_recurring',
        'recurrence_rule',
        'parent_event_id',
        'reminder_times',
        'email_reminder',
        'sms_reminder',
        'last_reminder_sent',
        'attendees',
        'created_by',
        'is_private',
        'color',
        'tags',
        'priority',
        'notes',
        'custom_fields',
        'external_id',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'last_reminder_sent' => 'datetime',
        'reminder_times' => 'array',
        'attendees' => 'array',
        'tags' => 'array',
        'custom_fields' => 'array',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'email_reminder' => 'boolean',
        'sms_reminder' => 'boolean',
        'is_private' => 'boolean',
    ];

    protected $appends = [
        'duration_hours',
        'is_past',
        'is_today',
        'is_upcoming',
    ];

    /**
     * Relationship: Event belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Event belongs to a Task (optional)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship: Event belongs to an Assignment (optional)
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Relationship: Event belongs to an Opportunity (optional)
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Relationship: Event belongs to a Creator (User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Event belongs to a Parent Event (for recurring events)
     */
    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'parent_event_id');
    }

    /**
     * Get duration in hours
     */
    public function getDurationHoursAttribute(): float
    {
        if ($this->start_datetime && $this->end_datetime) {
            return $this->start_datetime->diffInHours($this->end_datetime);
        }
        return 0;
    }

    /**
     * Check if event is in the past
     */
    public function getIsPastAttribute(): bool
    {
        return $this->end_datetime && $this->end_datetime->isPast();
    }

    /**
     * Check if event is today
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->start_datetime && $this->start_datetime->isToday();
    }

    /**
     * Check if event is upcoming
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_datetime && $this->start_datetime->isFuture();
    }

    /**
     * Scope: Events in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_datetime', [$startDate, $endDate])
              ->orWhereBetween('end_datetime', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_datetime', '<=', $startDate)
                     ->where('end_datetime', '>=', $endDate);
              });
        });
    }

    /**
     * Scope: Events by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    /**
     * Scope: Today's events
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_datetime', today());
    }

    /**
     * Check if event conflicts with given time range
     */
    public function conflictsWith($startDatetime, $endDatetime): bool
    {
        return $this->start_datetime < $endDatetime && $this->end_datetime > $startDatetime;
    }

    /**
     * Send reminder notification
     */
    public function sendReminder()
    {
        if ($this->email_reminder && $this->reminder_times) {
            foreach ($this->reminder_times as $minutes) {
                $reminderTime = $this->start_datetime->subMinutes($minutes);
                if ($reminderTime->isPast() && (!$this->last_reminder_sent || $this->last_reminder_sent->lt($reminderTime))) {
                    // Send reminder logic here
                    \Log::info("Reminder sent for event {$this->id} to user {$this->user_id}");
                    $this->update(['last_reminder_sent' => now()]);
                    break;
                }
            }
        }
    }

    /**
     * Create recurring event instances
     */
    public function createRecurringInstances($endDate = null)
    {
        if (!$this->is_recurring || !$this->recurrence_rule) {
            return;
        }

        // Implementation for creating recurring event instances
        // This would parse the recurrence rule and create multiple event records
    }
}
