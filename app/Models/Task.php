<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'created_by',
        'title',
        'description',
        'instructions',
        'priority',
        'status',
        'start_datetime',
        'end_datetime',
        'duration_minutes',
        'is_recurring',
        'recurring_pattern',
        'recurring_end_date',
        'location_type',
        'location_address',
        'location_coordinates',
        'location_instructions',
        'volunteers_needed',
        'volunteers_assigned',
        'required_skills',
        'special_requirements',
        'assignment_type',
        'allow_self_assignment',
        'assignment_deadline',
        'completion_notes',
        'completion_checklist',
        'requires_check_in',
        'requires_check_out',
        'estimated_hours',
        'budget_allocated',
        'equipment_needed',
        'safety_requirements',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'recurring_end_date' => 'date',
        'assignment_deadline' => 'datetime',
        'recurring_pattern' => 'array',
        'required_skills' => 'array',
        'completion_checklist' => 'array',
        'equipment_needed' => 'array',
        'is_recurring' => 'boolean',
        'allow_self_assignment' => 'boolean',
        'requires_check_in' => 'boolean',
        'requires_check_out' => 'boolean',
        'budget_allocated' => 'decimal:2',
    ];

    protected $appends = [
        'status_label',
        'priority_label',
        'duration_hours',
        'spots_remaining',
        'is_overdue',
        'can_assign_volunteers',
    ];

    /**
     * Relationship: Task belongs to an Opportunity
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Relationship: Task belongs to a creator (User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Task has many Assignments
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Relationship: Task has many Schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Relationship: Task has many Calendar Events
     */
    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /**
     * Get accepted assignments
     */
    public function acceptedAssignments()
    {
        return $this->assignments()->where('status', 'accepted');
    }

    /**
     * Get pending assignments
     */
    public function pendingAssignments()
    {
        return $this->assignments()->where('status', 'pending');
    }

    /**
     * Get completed assignments
     */
    public function completedAssignments()
    {
        return $this->assignments()->where('status', 'completed');
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get human-readable priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Low Priority',
            'medium' => 'Medium Priority',
            'high' => 'High Priority',
            'urgent' => 'Urgent',
            default => ucfirst($this->priority)
        };
    }

    /**
     * Get duration in hours
     */
    public function getDurationHoursAttribute(): float
    {
        if ($this->duration_minutes) {
            return round($this->duration_minutes / 60, 2);
        }

        if ($this->start_datetime && $this->end_datetime) {
            return $this->start_datetime->diffInMinutes($this->end_datetime) / 60;
        }

        return 0;
    }

    /**
     * Get remaining volunteer spots
     */
    public function getSpotsRemainingAttribute(): int
    {
        return max(0, $this->volunteers_needed - $this->volunteers_assigned);
    }

    /**
     * Check if task is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->end_datetime && $this->end_datetime->isPast() &&
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if volunteers can be assigned
     */
    public function getCanAssignVolunteersAttribute(): bool
    {
        return $this->status === 'published' &&
               $this->spots_remaining > 0 &&
               (!$this->assignment_deadline || $this->assignment_deadline->isFuture());
    }

    /**
     * Scope: Published tasks
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Tasks in date range
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
     * Scope: Tasks by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Tasks needing volunteers
     */
    public function scopeNeedingVolunteers($query)
    {
        return $query->whereRaw('volunteers_assigned < volunteers_needed')
                    ->where('status', 'published');
    }

    /**
     * Assign volunteer to task
     */
    public function assignVolunteer(User $volunteer, User $assignedBy, array $options = [])
    {
        if (!$this->can_assign_volunteers) {
            throw new \Exception('Cannot assign volunteers to this task.');
        }

        // Check for scheduling conflicts
        $conflicts = $this->checkSchedulingConflicts($volunteer);
        if (!empty($conflicts) && !($options['override_conflicts'] ?? false)) {
            throw new \Exception('Scheduling conflict detected: ' . implode(', ', $conflicts));
        }

        $assignment = Assignment::create([
            'task_id' => $this->id,
            'volunteer_id' => $volunteer->id,
            'assigned_by' => $assignedBy->id,
            'scheduled_start' => $this->start_datetime,
            'scheduled_end' => $this->end_datetime,
            'assignment_method' => $options['method'] ?? 'manual',
            'assignment_notes' => $options['notes'] ?? null,
            'has_conflict' => !empty($conflicts),
            'conflict_details' => !empty($conflicts) ? implode(', ', $conflicts) : null,
            'conflict_status' => !empty($conflicts) ? 'detected' : 'none',
        ]);

        // Update volunteers assigned count
        $this->increment('volunteers_assigned');

        // Create calendar event for volunteer
        $this->createCalendarEvent($volunteer, $assignment);

        return $assignment;
    }

    /**
     * Check for scheduling conflicts
     */
    public function checkSchedulingConflicts(User $volunteer): array
    {
        $conflicts = [];

        // Check existing assignments
        $existingAssignments = Assignment::where('volunteer_id', $volunteer->id)
            ->where('status', 'accepted')
            ->where(function($query) {
                $query->whereBetween('scheduled_start', [$this->start_datetime, $this->end_datetime])
                      ->orWhereBetween('scheduled_end', [$this->start_datetime, $this->end_datetime])
                      ->orWhere(function($q) {
                          $q->where('scheduled_start', '<=', $this->start_datetime)
                            ->where('scheduled_end', '>=', $this->end_datetime);
                      });
            })
            ->with('task')
            ->get();

        foreach ($existingAssignments as $assignment) {
            $conflicts[] = "Conflict with task: {$assignment->task->title}";
        }

        // Check volunteer availability
        $unavailableSchedules = Schedule::where('user_id', $volunteer->id)
            ->where('type', 'unavailability')
            ->where(function($query) {
                $query->whereBetween('start_datetime', [$this->start_datetime, $this->end_datetime])
                      ->orWhereBetween('end_datetime', [$this->start_datetime, $this->end_datetime])
                      ->orWhere(function($q) {
                          $q->where('start_datetime', '<=', $this->start_datetime)
                            ->where('end_datetime', '>=', $this->end_datetime);
                      });
            })
            ->get();

        foreach ($unavailableSchedules as $schedule) {
            $conflicts[] = "Volunteer unavailable: {$schedule->title}";
        }

        return $conflicts;
    }

    /**
     * Create calendar event for assignment
     */
    protected function createCalendarEvent(User $volunteer, Assignment $assignment)
    {
        CalendarEvent::create([
            'user_id' => $volunteer->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => 'assignment',
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'location' => $this->location_address,
            'location_type' => $this->location_type,
            'task_id' => $this->id,
            'assignment_id' => $assignment->id,
            'opportunity_id' => $this->opportunity_id,
            'created_by' => $this->created_by,
            'color' => $this->getColorByPriority(),
            'priority' => $this->getPriorityNumber(),
        ]);
    }

    /**
     * Get color based on priority
     */
    protected function getColorByPriority(): string
    {
        return match($this->priority) {
            'urgent' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#ffc107',
            'low' => '#28a745',
            default => '#007bff'
        };
    }

    /**
     * Get priority as number
     */
    protected function getPriorityNumber(): int
    {
        return match($this->priority) {
            'urgent' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            default => 3
        };
    }

    /**
     * Publish the task
     */
    public function publish()
    {
        $this->update(['status' => 'published']);

        // Create recurring tasks if needed
        if ($this->is_recurring && $this->recurring_pattern) {
            $this->createRecurringTasks();
        }
    }

    /**
     * Create recurring tasks
     */
    protected function createRecurringTasks()
    {
        // Implementation for creating recurring task instances
        // This would create multiple task records based on the recurring pattern
    }

    /**
     * Complete the task
     */
    public function complete($notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completion_notes' => $notes,
        ]);

        // Mark all assignments as completed
        $this->assignments()->where('status', 'accepted')->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
