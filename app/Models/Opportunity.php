<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    protected $fillable = [
        'organization_id', 'title', 'description', 'location', 'start_date', 'end_date', 'volunteers_needed'
    ];

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'opportunity_skills')
                    ->withPivot('required_level', 'is_required')
                    ->withTimestamps();
    }

    public function organization()
    {
        return $this->belongsTo(User::class, 'organization_id');
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Skill matches
    public function skillMatches()
    {
        return $this->hasMany(SkillMatch::class);
    }

    // Get required skills
    public function getRequiredSkillsAttribute()
    {
        return $this->skills()->wherePivot('is_required', true)->get();
    }

    // Get preferred skills
    public function getPreferredSkillsAttribute()
    {
        return $this->skills()->wherePivot('is_required', false)->get();
    }

    // Get skills by category
    public function getSkillsByCategory()
    {
        return $this->skills->groupBy('category');
    }
}