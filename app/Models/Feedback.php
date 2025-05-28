<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['application_id', 'rating', 'comments'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
