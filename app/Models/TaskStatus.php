<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    protected $fillable = ['application_id', 'status', 'remarks', 'updated_at'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
