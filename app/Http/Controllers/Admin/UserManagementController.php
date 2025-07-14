<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\VolunteerProfile;
use App\Models\OrganizationProfile;
use App\Services\NotificationService;

class UserManagementController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        // The middleware is already handled in routes
        $this->notificationService = $notificationService;
    }

    /**
     * Display all users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'volunteerProfile', 'organizationProfile']);

        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } else {
                $query->where('account_status', $request->status);
            }
        }

        // Search by name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json(['users' => $users]);
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load(['roles', 'volunteerProfile', 'organizationProfile', 'applications', 'opportunities']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show user edit form
     */
    public function edit(User $user)
    {
        $user->load(['roles', 'volunteerProfile', 'organizationProfile']);
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'account_status' => 'required|in:active,inactive,suspended,pending_approval',
            'admin_notes' => 'nullable|string|max:1000',
        ];

        $validatedData = $request->validate($rules);

        // Update user basic info
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'account_status' => $validatedData['account_status'],
            'admin_notes' => $validatedData['admin_notes'],
        ]);

        // Update roles
        $user->roles()->sync($validatedData['roles']);

        // Log the change
        $this->logUserChange($user, 'updated', 'User profile updated by admin');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!'
            ]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Activate user account
     */
    public function activate(User $user)
    {
        $user->update([
            'is_active' => true,
            'account_status' => 'active',
            'activated_by' => Auth::id(),
            'activated_at' => now(),
            'deactivated_by' => null,
            'deactivated_at' => null,
        ]);

        // Send notification to user
        $this->notificationService->notifySystem(
            $user,
            'Account Activated',
            'Your account has been activated by an administrator. You can now access all features.',
            ['activated_by' => Auth::user()->name]
        );

        $this->logUserChange($user, 'activated', 'Account activated by admin');

        return response()->json([
            'success' => true,
            'message' => 'User account activated successfully!'
        ]);
    }

    /**
     * Deactivate user account
     */
    public function deactivate(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user->update([
            'is_active' => false,
            'account_status' => 'inactive',
            'status_reason' => $request->reason,
            'deactivated_by' => Auth::id(),
            'deactivated_at' => now(),
        ]);

        // Send notification to user
        $this->notificationService->notifySystem(
            $user,
            'Account Deactivated',
            'Your account has been deactivated. Reason: ' . $request->reason,
            ['deactivated_by' => Auth::user()->name, 'reason' => $request->reason]
        );

        $this->logUserChange($user, 'deactivated', $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'User account deactivated successfully!'
        ]);
    }

    /**
     * Suspend user account
     */
    public function suspend(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365' // days
        ]);

        $user->update([
            'is_active' => false,
            'account_status' => 'suspended',
            'status_reason' => $request->reason,
            'deactivated_by' => Auth::id(),
            'deactivated_at' => now(),
        ]);

        // Send notification to user
        $this->notificationService->notifySystem(
            $user,
            'Account Suspended',
            'Your account has been suspended. Reason: ' . $request->reason,
            [
                'suspended_by' => Auth::user()->name,
                'reason' => $request->reason,
                'duration' => $request->duration
            ]
        );

        $this->logUserChange($user, 'suspended', $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'User account suspended successfully!'
        ]);
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'confirm' => 'required|accepted'
        ]);

        // Prevent deleting admin users
        if ($user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete admin users!'
            ], 403);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        // Log before deletion
        $this->logUserChange($user, 'deleted', $request->reason);

        // Delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "User {$userName} ({$userEmail}) deleted successfully!"
        ]);
    }

    /**
     * Bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $rules = [
            'action' => 'required|in:activate,deactivate,suspend,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'required_if:action,deactivate,suspend,delete|string|max:500'
        ];

        $validatedData = $request->validate($rules);

        $users = User::whereIn('id', $validatedData['user_ids'])->get();
        $count = 0;

        foreach ($users as $user) {
            // Skip admin users for dangerous actions
            if ($user->hasRole('admin') && in_array($validatedData['action'], ['suspend', 'delete'])) {
                continue;
            }

            switch ($validatedData['action']) {
                case 'activate':
                    $user->update([
                        'is_active' => true,
                        'account_status' => 'active',
                        'activated_by' => Auth::id(),
                        'activated_at' => now(),
                    ]);
                    $count++;
                    break;

                case 'deactivate':
                    $user->update([
                        'is_active' => false,
                        'account_status' => 'inactive',
                        'status_reason' => $validatedData['reason'],
                        'deactivated_by' => Auth::id(),
                        'deactivated_at' => now(),
                    ]);
                    $count++;
                    break;

                case 'suspend':
                    $user->update([
                        'is_active' => false,
                        'account_status' => 'suspended',
                        'status_reason' => $validatedData['reason'],
                        'deactivated_by' => Auth::id(),
                        'deactivated_at' => now(),
                    ]);
                    $count++;
                    break;

                case 'delete':
                    $user->delete();
                    $count++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} users processed successfully!"
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Send notification to user
        $this->notificationService->notifySystem(
            $user,
            'Password Reset',
            'Your password has been reset by an administrator. Please log in with your new password.',
            ['reset_by' => Auth::user()->name]
        );

        $this->logUserChange($user, 'password_reset', 'Password reset by admin');

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully!'
        ]);
    }

    /**
     * Log user changes
     */
    private function logUserChange(User $user, string $action, string $reason)
    {
        // This could be expanded to use a dedicated audit log table
        \Log::info("Admin action on user", [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => $action,
            'reason' => $reason,
            'timestamp' => now()
        ]);
    }
}
