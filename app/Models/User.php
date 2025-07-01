<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

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

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }
}

