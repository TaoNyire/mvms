<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'account_status',
        'status_reason',
        'activated_by',
        'activated_at',
        'deactivated_by',
        'deactivated_at',
        'last_login_at',
        'last_login_ip',
        'admin_notes',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function volunteerProfile()
    {
        return $this->hasOne(VolunteerProfile::class);
    }

    public function organizationProfile()
    {
        return $this->hasOne(OrganizationProfile::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'organization_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'volunteer_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Get user's phone number from profile
     */
    public function getPhoneAttribute()
    {
        if ($this->hasRole('volunteer') && $this->volunteerProfile) {
            return $this->volunteerProfile->phone;
        }
        if ($this->hasRole('organization') && $this->organizationProfile) {
            return $this->organizationProfile->phone;
        }
        return null;
    }

    /**
     * Get user's district from profile
     */
    public function getDistrictAttribute()
    {
        if ($this->hasRole('volunteer') && $this->volunteerProfile) {
            return $this->volunteerProfile->district;
        }
        if ($this->hasRole('organization') && $this->organizationProfile) {
            return $this->organizationProfile->district;
        }
        return null;
    }

    /**
     * Get user's region from profile
     */
    public function getRegionAttribute()
    {
        if ($this->hasRole('volunteer') && $this->volunteerProfile) {
            return $this->volunteerProfile->region;
        }
        if ($this->hasRole('organization') && $this->organizationProfile) {
            return $this->organizationProfile->region;
        }
        return null;
    }

    /**
     * Check if user profile is completed
     */
    public function getProfileCompletedAttribute()
    {
        if ($this->hasRole('volunteer')) {
            return $this->volunteerProfile && $this->volunteerProfile->completion_percentage >= 60;
        }
        if ($this->hasRole('organization')) {
            return $this->organizationProfile && $this->organizationProfile->completion_percentage >= 85;
        }
        return true; // Admin users don't need profile completion
    }

    // Skills relationship
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
                    ->withPivot('proficiency_level', 'years_experience', 'notes')
                    ->withTimestamps();
    }

    // Skill matches
    public function skillMatches()
    {
        return $this->hasMany(SkillMatch::class);
    }

    // Get user's skill categories
    public function getSkillCategoriesAttribute()
    {
        return $this->skills->pluck('category')->unique()->values();
    }

    // Get user's skills by category
    public function getSkillsByCategory()
    {
        return $this->skills->groupBy('category');
    }

    // Organization-specific skills relationship
    public function organizationSkills()
    {
        return $this->hasMany(Skill::class, 'organization_id')
                   ->where('skill_type', 'organization_specific');
    }

    // Get all skills available to this organization (global + organization-specific)
    public function availableSkills()
    {
        return Skill::forOrganization($this->id)->active();
    }

    /**
     * Get the user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's notification preferences
     */
    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }


}

