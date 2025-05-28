<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'volunteer_id', 'opportunity_id',
        'status', 'applied_at', 'responded_at'
    ];

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function taskStatus()
    {
        return $this->hasOne(TaskStatus::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }
}

