<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
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
            'other' => 'Other'
        ];
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