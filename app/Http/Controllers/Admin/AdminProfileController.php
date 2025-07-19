<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    /**
     * Show admin profile management page
     */
    public function show()
    {
        $admin = Auth::user();
        
        // Get admin activity statistics
        $stats = [
            'login_count' => $this->getLoginCount($admin),
            'last_login' => $admin->last_login_at,
            'account_created' => $admin->created_at,
            'total_actions' => $this->getTotalAdminActions($admin),
            'users_managed' => $this->getUsersManagedCount($admin),
            'organizations_approved' => $this->getOrganizationsApprovedCount($admin),
        ];

        // Get recent admin activities
        $recent_activities = $this->getRecentAdminActivities($admin);

        // Get security settings using existing fields and session data
        $security_settings = [
            'two_factor_enabled' => false, // Placeholder for future 2FA implementation
            'last_password_change' => session('admin_password_changed_at', $admin->created_at),
            'failed_login_attempts' => 0, // Placeholder for future implementation
            'active_sessions' => 1, // Placeholder for session management
        ];

        return view('admin.profile.show', compact('admin', 'stats', 'recent_activities', 'security_settings'));
    }

    /**
     * Show admin profile edit form
     */
    public function edit()
    {
        $admin = Auth::user();
        return view('admin.profile.edit', compact('admin'));
    }

    /**
     * Update admin profile
     */
    public function update(Request $request)
    {
        $admin = Auth::user();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'notification_preferences' => 'array',
            'notification_preferences.*' => 'boolean',
        ]);

        // Update basic profile information using existing User table fields
        $admin->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
        ]);

        // Store additional profile data in session or cache for now
        // In a real implementation, you might want to add these fields to the users table
        // or create a separate admin_profiles table
        session([
            'admin_profile_data' => [
                'phone' => $validatedData['phone'] ?? null,
                'bio' => $validatedData['bio'] ?? null,
                'notification_preferences' => $validatedData['notification_preferences'] ?? [],
                'updated_at' => now(),
            ]
        ]);

        // Log the profile update
        Log::info('Admin profile updated', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'updated_fields' => array_keys($validatedData),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.profile.show')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('admin.profile.change-password');
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request)
    {
        $admin = Auth::user();

        $validatedData = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // Verify current password
        if (!Hash::check($validatedData['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password using existing User table
        $admin->update([
            'password' => Hash::make($validatedData['password']),
            'updated_at' => now(), // Use existing updated_at field
        ]);

        // Store password change timestamp in session for tracking
        session(['admin_password_changed_at' => now()]);

        // Log password change
        Log::info('Admin password changed', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.profile.show')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Show security settings
     */
    public function security()
    {
        $admin = Auth::user();
        
        // Get security-related information
        $security_info = [
            'recent_logins' => $this->getRecentLogins($admin),
            'active_sessions' => $this->getActiveSessions($admin),
            'security_events' => $this->getSecurityEvents($admin),
            'login_history' => $this->getLoginHistory($admin),
        ];

        return view('admin.profile.security', compact('admin', 'security_info'));
    }

    /**
     * Get admin login count (placeholder - implement based on your logging system)
     */
    private function getLoginCount($admin)
    {
        // This would typically query a login_logs table
        return 150; // Placeholder
    }

    /**
     * Get total admin actions count
     */
    private function getTotalAdminActions($admin)
    {
        // This would query your audit logs or activity logs
        return 1250; // Placeholder
    }

    /**
     * Get count of users managed by this admin
     */
    private function getUsersManagedCount($admin)
    {
        // This would query user management logs
        return 45; // Placeholder
    }

    /**
     * Get count of organizations approved by this admin
     */
    private function getOrganizationsApprovedCount($admin)
    {
        // This would query organization approval logs
        return 23; // Placeholder
    }

    /**
     * Get recent admin activities
     */
    private function getRecentAdminActivities($admin)
    {
        // This would query your activity logs
        return [
            [
                'action' => 'Approved organization',
                'target' => 'Community Health Initiative',
                'timestamp' => now()->subHours(2),
            ],
            [
                'action' => 'Updated user status',
                'target' => 'john.doe@example.com',
                'timestamp' => now()->subHours(5),
            ],
            [
                'action' => 'Generated system report',
                'target' => 'Monthly User Statistics',
                'timestamp' => now()->subDay(),
            ],
        ];
    }

    /**
     * Get recent login information
     */
    private function getRecentLogins($admin)
    {
        // This would query login logs
        return [
            ['ip' => '192.168.1.100', 'timestamp' => now()->subHours(1), 'location' => 'Lilongwe, Malawi'],
            ['ip' => '192.168.1.100', 'timestamp' => now()->subDay(), 'location' => 'Lilongwe, Malawi'],
            ['ip' => '10.0.0.50', 'timestamp' => now()->subDays(2), 'location' => 'Blantyre, Malawi'],
        ];
    }

    /**
     * Get active sessions
     */
    private function getActiveSessions($admin)
    {
        // This would query active sessions
        return [
            ['device' => 'Chrome on Windows', 'ip' => '192.168.1.100', 'last_activity' => now()->subMinutes(5)],
        ];
    }

    /**
     * Get security events
     */
    private function getSecurityEvents($admin)
    {
        // Get real security events from database
        $events = [];

        // Add password change events if available
        if ($admin->password_changed_at) {
            $events[] = ['event' => 'Password changed', 'timestamp' => $admin->password_changed_at];
        }

        // Add profile update events
        if ($admin->updated_at != $admin->created_at) {
            $events[] = ['event' => 'Profile updated', 'timestamp' => $admin->updated_at];
        }

        // Add account creation event
        $events[] = ['event' => 'Account created', 'timestamp' => $admin->created_at];

        return collect($events)->sortByDesc('timestamp')->take(10)->values()->all();
    }

    /**
     * Get login history
     */
    private function getLoginHistory($admin)
    {
        // Get real login history from database
        $history = [];

        // If we have last login data, create a basic history
        if ($admin->last_login_at) {
            $history[] = [
                'date' => $admin->last_login_at->format('Y-m-d'),
                'successful' => 1,
                'failed' => 0
            ];
        }

        // Add account creation as first login
        if ($admin->created_at) {
            $history[] = [
                'date' => $admin->created_at->format('Y-m-d'),
                'successful' => 1,
                'failed' => 0
            ];
        }

        return collect($history)->unique('date')->sortByDesc('date')->take(7)->values();
    }
}
