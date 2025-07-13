<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'is_active',
        'organization_id',
        'skill_type',
        'required_proficiency_level',
        'priority'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(User::class, 'organization_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_skills')
                    ->withPivot('proficiency_level', 'years_experience', 'notes')
                    ->withTimestamps();
    }

    public function volunteers()
    {
        return $this->belongsToMany(VolunteerProfile::class, 'volunteer_skills');
    }

    public function opportunities()
    {
        return $this->belongsToMany(Opportunity::class, 'opportunity_skills')
                    ->withPivot('required_level', 'is_required')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeGlobal($query)
    {
        return $query->where('skill_type', 'global');
    }

    public function scopeOrganizationSpecific($query, $organizationId = null)
    {
        $query = $query->where('skill_type', 'organization_specific');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query;
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where(function($q) use ($organizationId) {
            $q->where('skill_type', 'global')
              ->orWhere(function($subQ) use ($organizationId) {
                  $subQ->where('skill_type', 'organization_specific')
                       ->where('organization_id', $organizationId);
              });
        });
    }

    // Helper methods
    public static function getCategories()
    {
        return [
            'technical' => 'Technical Skills',
            'communication' => 'Communication',
            'leadership' => 'Leadership',
            'creative' => 'Creative',
            'analytical' => 'Analytical',
            'interpersonal' => 'Interpersonal',
            'organizational' => 'Organizational',
            'physical' => 'Physical',
            'language' => 'Language',
            'custom' => 'Custom Skills',
            'other' => 'Other'
        ];
    }

    public static function getSkillTypes()
    {
        return [
            'global' => 'Global Skills',
            'organization_specific' => 'Organization Specific'
        ];
    }

    public function isGlobal()
    {
        return $this->skill_type === 'global';
    }

    public function isOrganizationSpecific()
    {
        return $this->skill_type === 'organization_specific';
    }

    public function belongsToOrganization($organizationId)
    {
        return $this->organization_id == $organizationId;
    }

    public static function getProficiencyLevels()
    {
        return [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            'expert' => 'Expert'
        ];
    }

    public function getCategoryDisplayAttribute()
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? ucfirst($this->category);
    }
}