<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationProfile extends Model
{
    protected $fillable = [
        'user_id',
        // Basic Organization Information
        'org_name',
        'description',
        'mission',
        'vision',
        'sector',
        'org_type',

        // Registration Information
        'registration_number',
        'is_registered',
        'registration_date',
        'registration_authority',
        'tax_id',

        // Contact Information
        'physical_address',
        'district',
        'region',
        'postal_address',
        'email',
        'phone',
        'alternative_phone',
        'website',
        'social_media',

        // Operational Information
        'focus_areas',
        'target_beneficiaries',
        'geographical_coverage',
        'staff_count',
        'volunteer_count',
        'annual_budget',
        'established_date',

        // Capacity and Resources
        'services_offered',
        'resources_available',
        'partnerships',
        'achievements',
        'current_projects',

        // Documents
        'registration_certificate_path',
        'registration_certificate_original_name',
        'tax_clearance_path',
        'tax_clearance_original_name',
        'other_documents',

        // Contact Person Information
        'contact_person_name',
        'contact_person_title',
        'contact_person_phone',
        'contact_person_email',

        // Profile Status
        'is_complete',
        'is_verified',
        'active',
        'status',
        'profile_completed_at',
        'verified_at',
        'approved_at',
        'rejected_at',
        'approved_by',
        'rejected_by',
        'rejection_reason',

        // Additional Information
        'additional_info',
        'certifications',
        'volunteer_requirements',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'focus_areas' => 'array',
        'target_beneficiaries' => 'array',
        'geographical_coverage' => 'array',
        'services_offered' => 'array',
        'resources_available' => 'array',
        'partnerships' => 'array',
        'other_documents' => 'array',
        'certifications' => 'array',
        'is_registered' => 'boolean',
        'is_complete' => 'boolean',
        'is_verified' => 'boolean',
        'active' => 'boolean',
        'registration_date' => 'date',
        'established_date' => 'date',
        'profile_completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'annual_budget' => 'decimal:2',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'status' => 'pending',
        'active' => true,
        'is_registered' => true,
        'is_complete' => false,
        'is_verified' => false,
    ];

    protected $appends = [
        'registration_certificate_url',
        'tax_clearance_url',
        'completion_percentage',
        'is_profile_complete'
    ];

    /**
     * Relationship: OrganizationProfile belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get registration certificate URL
     */
    public function getRegistrationCertificateUrlAttribute()
    {
        return $this->registration_certificate_path ? asset('storage/' . $this->registration_certificate_path) : null;
    }

    /**
     * Get tax clearance URL
     */
    public function getTaxClearanceUrlAttribute()
    {
        return $this->tax_clearance_path ? asset('storage/' . $this->tax_clearance_path) : null;
    }

    /**
     * Calculate profile completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        $requiredFields = [
            'org_name',
            'description',
            'mission',
            'sector',
            'org_type',
            'physical_address',
            'district',
            'region',
            'email',
            'phone',
            'focus_areas',
            'contact_person_name',
            'contact_person_email'
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
        return $this->completion_percentage >= 85; // 85% completion required (11 out of 13 fields)
    }

    /**
     * Mark profile as complete
     */
    public function markAsComplete()
    {
        $this->update([
            'is_complete' => true,
            'profile_completed_at' => now(),
            'status' => 'pending' // Pending verification
        ]);
    }

    /**
     * Get formatted focus areas
     */
    public function getFormattedFocusAreasAttribute()
    {
        if (!$this->focus_areas || !is_array($this->focus_areas)) {
            return 'Not specified';
        }

        return implode(', ', $this->focus_areas);
    }

    /**
     * Get organization age in years
     */
    public function getOrganizationAgeAttribute()
    {
        if (!$this->established_date) {
            return null;
        }

        return now()->diffInYears($this->established_date);
    }
}
