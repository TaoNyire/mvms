<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApplicationTaskStatus extends Model
{
    protected $table = 'application_task_status';
    
    protected $fillable = [
        'application_id',
        'status',
        'started_at',
        'completed_at',
        'completion_notes',
        'work_evidence'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'work_evidence' => 'array'
    ];

    // Relationships
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompletedBetween($query, $startDate, $endDate)
    {
        return $query->where('status', 'completed')
                    ->whereBetween('completed_at', [$startDate, $endDate]);
    }

    // Helper methods
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInDays($this->completed_at);
        }
        return null;
    }

    public function getDurationHoursAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInHours($this->completed_at);
        }
        return null;
    }

    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function markAsCompleted($notes = null, $evidence = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $notes,
            'work_evidence' => $evidence
        ]);
    }

    public function markAsQuit($notes = null)
    {
        $this->update([
            'status' => 'quit',
            'completed_at' => now(),
            'completion_notes' => $notes
        ]);
    }
}
