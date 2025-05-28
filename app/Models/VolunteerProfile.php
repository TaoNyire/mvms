<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerProfile extends Model
{
    protected $fillable = ['user_id', 'bio', 'location', 'availability'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
{
    return $this->belongsToMany(Skill::class, 'volunteer_skills');
}
}

