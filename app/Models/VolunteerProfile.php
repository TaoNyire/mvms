<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VolunteerProfile extends Model
{
    protected $fillable = [
        'user_id',
        // Basic Information
        'full_name',
        'bio',
        'date_of_birth',
        'gender',

        // Contact Details
        'phone',
        'alternative_phone',
        'emergency_contact_name',
        'emergency_contact_phone',

        // Location Information
        'physical_address',
        'district',
        'region',
        'postal_code',

        // Skills and Interests
        'skills',
        'interests',
        'experience_description',
        'languages',

        // Availability
        'available_days',
        'available_time_start',
        'available_time_end',
        'preferred_locations',
        'can_travel',
        'max_travel_distance',
        'availability_type',

        // Documents
        'id_document_path',
        'id_document_original_name',
        'cv_path',
        'cv_original_name',
        'certificates',

        // Education and Qualifications
        'education_level',
        'field_of_study',
        'institution',
        'graduation_year',

        // Professional Information
        'current_occupation',
        'employer',
        'professional_skills',

        // Volunteer Preferences
        'preferred_volunteer_types',
        'causes_interested_in',
        'has_volunteered_before',
        'previous_volunteer_experience',

        // Profile Status
        'is_complete',
        'is_verified',
        'is_active',
        'profile_completed_at',
        'last_updated_at',

        // Additional Information
        'special_requirements',
        'motivation',
        'references',
    ];

    protected $casts = [
        'skills' => 'array',
        'interests' => 'array',
        'languages' => 'array',
        'available_days' => 'array',
        'preferred_locations' => 'array',
        'certificates' => 'array',
        'preferred_volunteer_types' => 'array',
        'causes_interested_in' => 'array',
        'references' => 'array',
        'can_travel' => 'boolean',
        'has_volunteered_before' => 'boolean',
        'is_complete' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
        'available_time_start' => 'datetime:H:i',
        'available_time_end' => 'datetime:H:i',
        'profile_completed_at' => 'datetime',
        'last_updated_at' => 'datetime',
    ];

    protected $appends = [
        'cv_url',
        'id_document_url',
        'completion_percentage',
        'is_profile_complete'
    ];

    /**
     * Relationship: VolunteerProfile belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: VolunteerProfile has many skills
     */
    public function skillsRelation(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'volunteer_skills')
                    ->withPivot('proficiency_level', 'years_experience')
                    ->withTimestamps();
    }

    /**
     * Get CV URL
     */
    public function getCvUrlAttribute()
    {
        return $this->cv_path ? asset('storage/' . $this->cv_path) : null;
    }

    /**
     * Get ID Document URL
     */
    public function getIdDocumentUrlAttribute()
    {
        return $this->id_document_path ? asset('storage/' . $this->id_document_path) : null;
    }

    /**
     * Calculate profile completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        $requiredFields = [
            'full_name',
            'phone',
            'physical_address',
            'district',
            'region',
            'bio',
            'skills',
            'available_days',
            'availability_type',
            'education_level',
            'motivation'
        ];

        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $filledFields++;
            }
        }

        return round(($filledFields / count($requiredFields)) * 100);
    }

    /**
     * Check if profile is complete
     */
    public function getIsProfileCompleteAttribute()
    {
        return $this->completion_percentage >= 80; // 80% completion required
    }

    /**
     * Mark profile as complete
     */
    public function markAsComplete()
    {
        $this->update([
            'is_complete' => true,
            'profile_completed_at' => now(),
            'last_updated_at' => now()
        ]);
    }

    /**
     * Get formatted availability
     */
    public function getFormattedAvailabilityAttribute()
    {
        if (!$this->available_days) {
            return 'Not specified';
        }

        $days = implode(', ', array_map('ucfirst', $this->available_days));
        $time = '';

        if ($this->available_time_start && $this->available_time_end) {
            $time = " ({$this->available_time_start} - {$this->available_time_end})";
        }

        return $days . $time;
    }
}