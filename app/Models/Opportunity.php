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
        return $this->belongsToMany(Skill::class, 'opportunity_skills');
    }

    public function organization()
    {
        return $this->belongsTo(User::class, 'organization_id');
    }
}