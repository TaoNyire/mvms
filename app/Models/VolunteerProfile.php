<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerProfile extends Model
{
    protected $fillable = [
        'user_id', 'bio', 'location', 'district', 'region', 'availability',
        'cv', 'cv_original_name', 'qualifications', 'qualifications_original_name'
    ];

    protected $appends = ['cv_url', 'qualifications_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'volunteer_skills');
    }

    public function getCvUrlAttribute()
    {
        return $this->cv ? asset('storage/' . $this->cv) : null;
    }

    public function getQualificationsUrlAttribute()
    {
        return $this->qualifications ? asset('storage/' . $this->qualifications) : null;
    }
}