<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = ['name', 'email', 'password','status'];
    protected $hidden = ['password', 'remember_token'];

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
}

