<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'description', 'color'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    // Get user count for this role
    public function getUserCountAttribute()
    {
        return $this->users()->count();
    }

    // Get permission names array
    public function getPermissionNamesAttribute()
    {
        return $this->permissions()->pluck('name')->toArray();
    }
}

