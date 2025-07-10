<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users and specific routes
        if (Auth::check() && $this->shouldLog($request)) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    private function shouldLog(Request $request)
    {
        $method = $request->method();
        $path = $request->path();

        // Log POST, PUT, DELETE requests to API endpoints
        if (in_array($method, ['POST', 'PUT', 'DELETE']) && str_starts_with($path, 'api/')) {
            return true;
        }

        // Log specific GET requests
        $loggedGetRoutes = [
            'api/admin/security',
            'api/admin/maintenance'
        ];

        if ($method === 'GET' && in_array($path, $loggedGetRoutes)) {
            return true;
        }

        return false;
    }

    private function logActivity(Request $request, $response)
    {
        $user = Auth::user();
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();

        // Determine action and entity type from the route
        $actionData = $this->parseRoute($method, $path);
        
        $status = $statusCode >= 200 && $statusCode < 300 ? 'success' : 'failed';

        SystemLog::logUserAction(
            $actionData['action'],
            $actionData['entity_type'],
            $actionData['entity_id'],
            [
                'method' => $method,
                'path' => $path,
                'status_code' => $statusCode,
                'request_data' => $this->sanitizeRequestData($request->all())
            ],
            $status
        );
    }

    private function parseRoute($method, $path)
    {
        $segments = explode('/', $path);
        
        // Default values
        $action = strtolower($method);
        $entityType = 'Unknown';
        $entityId = null;

        // Parse admin routes
        if (isset($segments[1]) && $segments[1] === 'admin') {
            if (isset($segments[2])) {
                switch ($segments[2]) {
                    case 'users':
                        $entityType = 'User';
                        if ($method === 'POST') $action = 'create_user';
                        if ($method === 'PUT') $action = 'update_user';
                        if ($method === 'DELETE') $action = 'delete_user';
                        break;
                    case 'organizations':
                        $entityType = 'Organization';
                        if ($method === 'PUT' && isset($segments[4])) {
                            $action = $segments[4]; // approve or reject
                        }
                        break;
                    case 'roles':
                        $entityType = 'Role';
                        if ($method === 'POST') $action = 'create_role';
                        if ($method === 'PUT') $action = 'update_role';
                        if ($method === 'DELETE') $action = 'delete_role';
                        break;

                    case 'security':
                        $entityType = 'Security';
                        $action = 'view_security';
                        break;
                    case 'maintenance':
                        $entityType = 'Maintenance';
                        if (isset($segments[3]) && $segments[3] === 'backup') {
                            $action = 'run_backup';
                        } elseif (isset($segments[3]) && $segments[3] === 'task') {
                            $action = 'run_maintenance_task';
                        } else {
                            $action = 'view_maintenance';
                        }
                        break;
                }
            }
        }

        // Parse organization routes
        if (isset($segments[1]) && $segments[1] === 'organization') {
            if (isset($segments[2])) {
                switch ($segments[2]) {
                    case 'opportunities':
                        $entityType = 'Opportunity';
                        if ($method === 'POST') $action = 'create_opportunity';
                        if ($method === 'PUT') $action = 'update_opportunity';
                        if ($method === 'DELETE') $action = 'delete_opportunity';
                        break;
                    case 'applications':
                        $entityType = 'Application';
                        if ($method === 'PUT') $action = 'update_application_status';
                        break;
                }
            }
        }

        // Parse volunteer routes
        if (isset($segments[1]) && $segments[1] === 'volunteer') {
            if (isset($segments[2])) {
                switch ($segments[2]) {
                    case 'applications':
                        $entityType = 'Application';
                        if ($method === 'POST') $action = 'submit_application';
                        break;
                    case 'profile':
                        $entityType = 'Profile';
                        if ($method === 'PUT') $action = 'update_profile';
                        break;
                }
            }
        }

        // Extract entity ID if present
        if (isset($segments[3]) && is_numeric($segments[3])) {
            $entityId = (int) $segments[3];
        }

        return [
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ];
    }

    private function sanitizeRequestData($data)
    {
        // Remove sensitive data
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}
