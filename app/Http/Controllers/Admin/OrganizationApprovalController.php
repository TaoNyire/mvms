<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\OrganizationProfile;
use App\Services\NotificationService;

class OrganizationApprovalController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        // The middleware is already handled in routes
        $this->notificationService = $notificationService;
    }

    /**
     * Display organization approval queue
     */
    public function index(Request $request)
    {
        $query = OrganizationProfile::with(['user']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by organization name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('org_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $organizations = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json(['organizations' => $organizations]);
        }

        return view('admin.organizations.index', compact('organizations'));
    }

    /**
     * Show organization details for approval
     */
    public function show(OrganizationProfile $organization)
    {
        $organization->load(['user']);

        return view('admin.organizations.show', compact('organization'));
    }

    /**
     * Approve organization
     */
    public function approve(Request $request, OrganizationProfile $organization)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        // Update organization status
        $organization->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Activate the user account
        $organization->user->update([
            'is_active' => true,
            'account_status' => 'active',
            'activated_by' => Auth::id(),
            'activated_at' => now(),
        ]);

        // Update user account status
        $organization->user->update([
            'account_status' => 'active',
            'is_active' => true,
            'activated_by' => Auth::id(),
            'activated_at' => now(),
        ]);

        // Send approval notification
        $this->notificationService->notifySystem(
            $organization->user,
            'Organization Approved',
            "Congratulations! Your organization '{$organization->organization_name}' has been approved and is now active on the platform.",
            [
                'organization_name' => $organization->organization_name,
                'approved_by' => Auth::user()->name,
                'approved_at' => now()->toDateTimeString(),
            ]
        );

        // Log the approval
        $this->logOrganizationAction($organization, 'approved', $request->admin_notes);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization approved successfully!'
            ]);
        }

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization approved successfully!');
    }

    /**
     * Reject organization
     */
    public function reject(Request $request, OrganizationProfile $organization)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        // Update organization status
        $organization->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'admin_notes' => $request->rejection_reason,
        ]);

        // Update user account status
        $organization->user->update([
            'account_status' => 'suspended',
            'is_active' => false,
            'status_reason' => 'Organization registration rejected',
            'deactivated_by' => Auth::id(),
            'deactivated_at' => now(),
        ]);

        // Send rejection notification
        $this->notificationService->notifySystem(
            $organization->user,
            'Organization Registration Rejected',
            "We regret to inform you that your organization registration for '{$organization->organization_name}' has been rejected. Reason: {$request->rejection_reason}",
            [
                'organization_name' => $organization->organization_name,
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => Auth::user()->name,
                'rejected_at' => now()->toDateTimeString(),
            ]
        );

        // Log the rejection
        $this->logOrganizationAction($organization, 'rejected', $request->rejection_reason);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization rejected successfully!'
            ]);
        }

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization rejected successfully!');
    }

    /**
     * Request more information from organization
     */
    public function requestInfo(Request $request, OrganizationProfile $organization)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'required_documents' => 'nullable|array',
            'required_documents.*' => 'string'
        ]);

        // Update organization status
        $organization->update([
            'status' => 'info_requested',
            'admin_notes' => $request->message,
        ]);

        // Send information request notification
        $this->notificationService->notifySystem(
            $organization->user,
            'Additional Information Required',
            "We need additional information for your organization registration for '{$organization->organization_name}'. Please review the requirements and update your profile.",
            [
                'organization_name' => $organization->organization_name,
                'message' => $request->message,
                'required_documents' => $request->required_documents ?? [],
                'requested_by' => Auth::user()->name,
                'requested_at' => now()->toDateTimeString(),
            ]
        );

        // Log the action
        $this->logOrganizationAction($organization, 'info_requested', $request->message);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Information request sent successfully!'
            ]);
        }

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Information request sent successfully!');
    }

    /**
     * Suspend organization
     */
    public function suspend(Request $request, OrganizationProfile $organization)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        // Update organization status
        $organization->update([
            'status' => 'suspended',
            'admin_notes' => $request->reason,
        ]);

        // Update user account status
        $organization->user->update([
            'account_status' => 'suspended',
            'is_active' => false,
            'status_reason' => $request->reason,
            'deactivated_by' => Auth::id(),
            'deactivated_at' => now(),
        ]);

        // Send suspension notification
        $this->notificationService->notifySystem(
            $organization->user,
            'Organization Suspended',
            "Your organization '{$organization->organization_name}' has been suspended. Reason: {$request->reason}",
            [
                'organization_name' => $organization->organization_name,
                'reason' => $request->reason,
                'suspended_by' => Auth::user()->name,
                'suspended_at' => now()->toDateTimeString(),
            ]
        );

        // Log the suspension
        $this->logOrganizationAction($organization, 'suspended', $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Organization suspended successfully!'
        ]);
    }

    /**
     * Reactivate organization
     */
    public function reactivate(Request $request, OrganizationProfile $organization)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        // Update organization status
        $organization->update([
            'status' => 'approved',
            'admin_notes' => $request->notes,
        ]);

        // Update user account status
        $organization->user->update([
            'account_status' => 'active',
            'is_active' => true,
            'activated_by' => Auth::id(),
            'activated_at' => now(),
            'status_reason' => null,
        ]);

        // Send reactivation notification
        $this->notificationService->notifySystem(
            $organization->user,
            'Organization Reactivated',
            "Your organization '{$organization->organization_name}' has been reactivated and is now active on the platform.",
            [
                'organization_name' => $organization->organization_name,
                'reactivated_by' => Auth::user()->name,
                'reactivated_at' => now()->toDateTimeString(),
                'notes' => $request->notes,
            ]
        );

        // Log the reactivation
        $this->logOrganizationAction($organization, 'reactivated', $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Organization reactivated successfully!'
        ]);
    }

    /**
     * Bulk actions on organizations
     */
    public function bulkAction(Request $request)
    {
        $rules = [
            'action' => 'required|in:approve,reject,suspend',
            'organization_ids' => 'required|array',
            'organization_ids.*' => 'exists:organization_profiles,id',
            'reason' => 'required_if:action,reject,suspend|string|max:1000'
        ];

        $validatedData = $request->validate($rules);

        $organizations = OrganizationProfile::whereIn('id', $validatedData['organization_ids'])->get();
        $count = 0;

        foreach ($organizations as $organization) {
            switch ($validatedData['action']) {
                case 'approve':
                    $organization->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                    ]);
                    $organization->user->update([
                        'account_status' => 'active',
                        'is_active' => true,
                        'activated_by' => Auth::id(),
                        'activated_at' => now(),
                    ]);
                    $count++;
                    break;

                case 'reject':
                    $organization->update([
                        'status' => 'rejected',
                        'rejected_by' => Auth::id(),
                        'rejected_at' => now(),
                        'rejection_reason' => $validatedData['reason'],
                    ]);
                    $organization->user->update([
                        'account_status' => 'suspended',
                        'is_active' => false,
                        'status_reason' => 'Organization registration rejected',
                        'deactivated_by' => Auth::id(),
                        'deactivated_at' => now(),
                    ]);
                    $count++;
                    break;

                case 'suspend':
                    $organization->update([
                        'status' => 'suspended',
                        'admin_notes' => $validatedData['reason'],
                    ]);
                    $organization->user->update([
                        'account_status' => 'suspended',
                        'is_active' => false,
                        'status_reason' => $validatedData['reason'],
                        'deactivated_by' => Auth::id(),
                        'deactivated_at' => now(),
                    ]);
                    $count++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} organizations processed successfully!"
        ]);
    }

    /**
     * Log organization actions
     */
    private function logOrganizationAction(OrganizationProfile $organization, string $action, string $reason = null)
    {
        \Log::info("Admin action on organization", [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
            'organization_id' => $organization->id,
            'organization_name' => $organization->organization_name,
            'user_id' => $organization->user_id,
            'user_email' => $organization->user->email,
            'action' => $action,
            'reason' => $reason,
            'timestamp' => now()
        ]);
    }
}
