<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'application_id',
        'from_type',
        'from_user_id',
        'rating',
        'comments'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function volunteer()
    {
        return $this->hasOneThrough(User::class, Application::class, 'id', 'id', 'application_id', 'volunteer_id');
    }

    public function organization()
    {
        return $this->hasOneThrough(User::class, Application::class, 'id', 'id', 'application_id', 'organization_id');
    }
}
