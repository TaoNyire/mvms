<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'is_all_day',
        'timezone',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_end_date',
        'recurrence_count',
        'availability_type',
        'max_hours_per_day',
        'max_hours_per_week',
        'preferred_time_slots',
        'preferred_locations',
        'max_travel_distance',
        'remote_work_available',
        'assignment_id',
        'task_id',
        'priority',
        'is_flexible',
        'notes',
        'tags',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'recurrence_end_date' => 'date',
        'recurrence_days' => 'array',
        'preferred_time_slots' => 'array',
        'preferred_locations' => 'array',
        'tags' => 'array',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'remote_work_available' => 'boolean',
        'is_flexible' => 'boolean',
    ];

    /**
     * Relationship: Schedule belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Schedule belongs to an Assignment (optional)
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Relationship: Schedule belongs to a Task (optional)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Scope: Availability schedules
     */
    public function scopeAvailability($query)
    {
        return $query->where('type', 'availability');
    }

    /**
     * Scope: Unavailability schedules
     */
    public function scopeUnavailability($query)
    {
        return $query->where('type', 'unavailability');
    }

    /**
     * Scope: Schedules in date range
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
     * Check if schedule conflicts with given time range
     */
    public function conflictsWith($startDatetime, $endDatetime): bool
    {
        return $this->start_datetime < $endDatetime && $this->end_datetime > $startDatetime;
    }

    /**
     * Get duration in hours
     */
    public function getDurationHours(): float
    {
        if ($this->start_datetime && $this->end_datetime) {
            $diff = $this->start_datetime->diff($this->end_datetime);
            return $diff->h + $diff->i / 60;
        }
        return 0;
    }
}
