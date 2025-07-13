<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'opportunity_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'assigned_volunteers',
        'completion_notes',
        'completed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now()->toDateString())
                    ->where('status', 'active');
    }

    // Helper methods
    public function isExpired()
    {
        return $this->end_date < now()->toDateString() && $this->status === 'active';
    }

    public function markAsCompleted($notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $notes
        ]);
    }

    public function getDurationInDaysAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getActiveVolunteersAttribute()
    {
        return $this->applications()
                   ->where('status', 'accepted')
                   ->where('confirmation_status', 'confirmed')
                   ->count();
    }
}
