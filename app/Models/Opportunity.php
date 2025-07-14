<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        // Basic Information
        'title',
        'description',
        'requirements',
        'benefits',

        // Skills and Categories
        'required_skills',
        'category',
        'type',
        'urgency',

        // Location and Logistics
        'location_type',
        'address',
        'district',
        'region',
        'latitude',
        'longitude',

        // Timing
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'recurring_days',
        'duration_hours',
        'application_deadline',

        // Volunteer Requirements
        'volunteers_needed',
        'volunteers_recruited',
        'min_age',
        'max_age',
        'preferred_gender',
        'required_languages',
        'requires_background_check',
        'requires_training',
        'training_details',

        // Compensation and Benefits
        'is_paid',
        'payment_amount',
        'payment_frequency',
        'provides_transport',
        'provides_meals',
        'provides_accommodation',
        'other_benefits',

        // Contact Information
        'contact_person',
        'contact_phone',
        'contact_email',

        // Status and Management
        'status',
        'is_featured',
        'views_count',
        'applications_count',
        'published_at',
        'completed_at',

        // Additional Information
        'tags',
        'special_instructions',
        'attachments',
        'cancellation_reason',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'recurring_days' => 'array',
        'preferred_gender' => 'array',
        'required_languages' => 'array',
        'tags' => 'array',
        'attachments' => 'array',
        'requires_background_check' => 'boolean',
        'requires_training' => 'boolean',
        'is_paid' => 'boolean',
        'provides_transport' => 'boolean',
        'provides_meals' => 'boolean',
        'provides_accommodation' => 'boolean',
        'is_featured' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'application_deadline' => 'datetime',
        'published_at' => 'datetime',
        'completed_at' => 'datetime',
        'payment_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = [
        'is_active',
        'is_full',
        'days_until_start',
        'formatted_duration',
        'application_status',
        'spots_remaining'
    ];

    /**
     * Relationship: Opportunity belongs to an Organization (User)
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organization_id');
    }

    /**
     * Relationship: Opportunity has many Applications
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Relationship: Get accepted applications
     */
    public function acceptedApplications(): HasMany
    {
        return $this->hasMany(Application::class)->where('status', 'accepted');
    }

    /**
     * Relationship: Get pending applications
     */
    public function pendingApplications(): HasMany
    {
        return $this->hasMany(Application::class)->where('status', 'pending');
    }

    /**
     * Check if opportunity is currently active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'published' &&
               (!$this->application_deadline || $this->application_deadline->isFuture()) &&
               $this->start_date->isFuture();
    }

    /**
     * Check if opportunity is full
     */
    public function getIsFullAttribute(): bool
    {
        return $this->volunteers_recruited >= $this->volunteers_needed;
    }

    /**
     * Get days until opportunity starts
     */
    public function getDaysUntilStartAttribute(): int
    {
        return max(0, now()->diffInDays($this->start_date, false));
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_hours) {
            return 'Duration not specified';
        }

        if ($this->duration_hours < 24) {
            return $this->duration_hours . ' hour' . ($this->duration_hours > 1 ? 's' : '');
        }

        $days = floor($this->duration_hours / 24);
        $hours = $this->duration_hours % 24;

        $result = $days . ' day' . ($days > 1 ? 's' : '');
        if ($hours > 0) {
            $result .= ' ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        return $result;
    }

    /**
     * Get application status for deadline
     */
    public function getApplicationStatusAttribute(): string
    {
        if ($this->status !== 'published') {
            return 'Not published';
        }

        if ($this->is_full) {
            return 'Full';
        }

        if ($this->application_deadline && $this->application_deadline->isPast()) {
            return 'Applications closed';
        }

        if ($this->start_date->isPast()) {
            return 'Started';
        }

        return 'Open for applications';
    }

    /**
     * Get remaining spots
     */
    public function getSpotsRemainingAttribute(): int
    {
        return max(0, $this->volunteers_needed - $this->volunteers_recruited);
    }

    /**
     * Scope: Published opportunities
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Active opportunities (published and accepting applications)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
                    ->where('start_date', '>', now())
                    ->where(function($q) {
                        $q->whereNull('application_deadline')
                          ->orWhere('application_deadline', '>', now());
                    });
    }

    /**
     * Scope: Filter by location
     */
    public function scopeInLocation($query, $district = null, $region = null)
    {
        if ($district) {
            $query->where('district', $district);
        }
        if ($region) {
            $query->where('region', $region);
        }
        return $query;
    }

    /**
     * Scope: Filter by category
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by skills
     */
    public function scopeWithSkills($query, array $skills)
    {
        return $query->where(function($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('required_skills', $skill);
            }
        });
    }

    /**
     * Calculate match score for a volunteer
     */
    public function calculateMatchScore(VolunteerProfile $volunteer): int
    {
        $score = 0;
        $maxScore = 100;

        // Skills match (40 points)
        if ($this->required_skills && $volunteer->skills) {
            $matchingSkills = array_intersect($this->required_skills, $volunteer->skills);
            $skillScore = (count($matchingSkills) / count($this->required_skills)) * 40;
            $score += $skillScore;
        }

        // Location match (25 points)
        if ($this->district === $volunteer->district) {
            $score += 20;
        } elseif ($this->region === $volunteer->region) {
            $score += 10;
        }

        // Availability match (20 points)
        if ($volunteer->available_days && $this->recurring_days) {
            $matchingDays = array_intersect($volunteer->available_days, $this->recurring_days);
            if (count($matchingDays) > 0) {
                $score += 15;
            }
        }

        // Travel capability (10 points)
        if ($volunteer->can_travel && $this->district !== $volunteer->district) {
            $score += 10;
        }

        // Experience relevance (5 points)
        if ($volunteer->experience_description &&
            stripos($volunteer->experience_description, $this->category) !== false) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    /**
     * Mark opportunity as published
     */
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Update volunteers recruited count
     */
    public function updateVolunteersCount()
    {
        $this->update([
            'volunteers_recruited' => $this->acceptedApplications()->count()
        ]);
    }
}
