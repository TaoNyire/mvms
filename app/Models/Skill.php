<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name'];

    public function volunteers()
    {
        return $this->belongsToMany(VolunteerProfile::class, 'volunteer_skills');
    }

    public function opportunities()
    {
        return $this->belongsToMany(Opportunity::class, 'opportunity_skills');
    }
}