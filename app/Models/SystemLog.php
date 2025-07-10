<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SystemLog extends Model
{
    protected $fillable = [
        'type',
        'action',
        'entity_type',
        'entity_id',
        'user_id',
        'user_email',
        'ip_address',
        'user_agent',
        'details',
        'status',
        'description'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static methods for easy logging
    public static function logLogin($user, $status = 'success', $details = [])
    {
        return self::create([
            'type' => 'login',
            'action' => 'user_login',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'details' => $details,
            'status' => $status,
            'description' => $status === 'success' 
                ? "User {$user->email} logged in successfully"
                : "Failed login attempt for {$user->email}"
        ]);
    }

    public static function logFailedLogin($email, $details = [])
    {
        return self::create([
            'type' => 'login',
            'action' => 'failed_login',
            'entity_type' => 'User',
            'user_email' => $email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'details' => $details,
            'status' => 'failed',
            'description' => "Failed login attempt for {$email}"
        ]);
    }

    public static function logLogout($user)
    {
        return self::create([
            'type' => 'logout',
            'action' => 'user_logout',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'status' => 'success',
            'description' => "User {$user->email} logged out"
        ]);
    }

    public static function logUserAction($action, $entityType, $entityId = null, $details = [], $status = 'success')
    {
        $user = Auth::user();
        $description = self::generateDescription($action, $entityType, $user, $details);

        return self::create([
            'type' => 'user_action',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'details' => $details,
            'status' => $status,
            'description' => $description
        ]);
    }

    public static function logSystemEvent($action, $details = [], $status = 'success')
    {
        $user = Auth::user();
        
        return self::create([
            'type' => 'system',
            'action' => $action,
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'details' => $details,
            'status' => $status,
            'description' => self::generateSystemDescription($action, $details)
        ]);
    }

    public static function logSecurityEvent($action, $details = [], $status = 'warning')
    {
        $user = Auth::user();
        
        return self::create([
            'type' => 'security',
            'action' => $action,
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'details' => $details,
            'status' => $status,
            'description' => self::generateSecurityDescription($action, $details)
        ]);
    }

    private static function generateDescription($action, $entityType, $user, $details)
    {
        $userEmail = $user ? $user->email : 'System';
        
        switch ($action) {
            case 'create':
                return "{$userEmail} created a new {$entityType}";
            case 'update':
                return "{$userEmail} updated {$entityType}";
            case 'delete':
                return "{$userEmail} deleted {$entityType}";
            case 'approve':
                return "{$userEmail} approved {$entityType}";
            case 'reject':
                return "{$userEmail} rejected {$entityType}";
            default:
                return "{$userEmail} performed {$action} on {$entityType}";
        }
    }

    private static function generateSystemDescription($action, $details)
    {
        switch ($action) {
            case 'backup':
                return "System backup completed";
            case 'maintenance_task':
                return "Maintenance task executed: " . ($details['task_name'] ?? 'Unknown');
            case 'settings_update':
                return "System settings updated";
            case 'role_created':
                return "New role created: " . ($details['role_name'] ?? 'Unknown');
            case 'role_updated':
                return "Role updated: " . ($details['role_name'] ?? 'Unknown');
            case 'role_deleted':
                return "Role deleted: " . ($details['role_name'] ?? 'Unknown');
            default:
                return "System event: {$action}";
        }
    }

    private static function generateSecurityDescription($action, $details)
    {
        switch ($action) {
            case 'multiple_failed_logins':
                return "Multiple failed login attempts detected for " . ($details['email'] ?? 'unknown user');
            case 'suspicious_activity':
                return "Suspicious activity detected";
            case 'unauthorized_access':
                return "Unauthorized access attempt";
            case 'permission_denied':
                return "Permission denied for action: " . ($details['attempted_action'] ?? 'unknown');
            default:
                return "Security event: {$action}";
        }
    }

    // Scopes for filtering
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
