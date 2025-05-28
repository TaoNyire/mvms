<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationProfile extends Model
{
    protected $fillable = [
        'user_id',
        'org_name',
        'description',
        'mission',
        'vision',
        'sector',
        'org_type',
        'registration_number',
        'is_registered',
        'physical_address',
        'district',
        'region',
        'email',
        'phone',
        'website',
        'focus_areas',
        'active',
        'status',
    ];

    /**
     * Relationship: OrganizationProfile belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
