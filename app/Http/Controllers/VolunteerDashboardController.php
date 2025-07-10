<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Opportunity;

class VolunteerDashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        // Example: You’ll add stats and graphs data here later
        return response()->json([
            'dashboard' => 'volunteer',
            'user' => $request->user(),
            'stats' => [
                'applications_count' => $request->user()->applications()->count(),
                // add more as needed
            ],
        ]);
    }

    /**
     * Get notifications for the authenticated volunteer
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = $request->user();

            // Get database notifications
            $dbNotifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($notification) {
                    $data = $notification->data;

                    // Determine notification type and message based on data
                    $type = 'info';
                    $title = 'Notification';
                    $message = 'You have a new notification';

                    if (isset($data['status'])) {
                        switch ($data['status']) {
                            case 'accepted':
                                $type = 'success';
                                $title = 'Application Accepted';
                                $message = "Your application for \"{$data['opportunity_title']}\" has been accepted!";
                                break;
                            case 'rejected':
                                $type = 'warning';
                                $title = 'Application Rejected';
                                $message = "Your application for \"{$data['opportunity_title']}\" was not accepted.";
                                break;
                            case 'pending':
                                $type = 'info';
                                $title = 'Application Received';
                                $message = "Your application for \"{$data['opportunity_title']}\" is being reviewed.";
                                break;
                        }
                    }

                    return [
                        'id' => $notification->id,
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'timestamp' => $notification->created_at->toISOString(),
                        'read' => $notification->read_at !== null,
                        'data' => $data
                    ];
                });

            // Get recent application updates as notifications
            $recentApplications = Application::where('volunteer_id', $user->id)
                ->where('updated_at', '>', now()->subDays(30))
                ->with(['opportunity'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($application) {
                    $type = 'info';
                    $title = 'Application Update';
                    $message = "Update on your application for \"{$application->opportunity->title}\"";

                    switch ($application->status) {
                        case 'accepted':
                            $type = 'success';
                            $title = 'Application Accepted';
                            $message = "Your application for \"{$application->opportunity->title}\" has been accepted!";
                            break;
                        case 'rejected':
                            $type = 'warning';
                            $title = 'Application Rejected';
                            $message = "Your application for \"{$application->opportunity->title}\" was not accepted.";
                            break;
                        case 'pending':
                            $type = 'info';
                            $title = 'Application Submitted';
                            $message = "Your application for \"{$application->opportunity->title}\" is being reviewed.";
                            break;
                    }

                    return [
                        'id' => 'app_' . $application->id,
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'timestamp' => $application->updated_at->toISOString(),
                        'read' => true, // Mark application updates as read by default
                        'data' => [
                            'application_id' => $application->id,
                            'opportunity_id' => $application->opportunity_id,
                            'opportunity_title' => $application->opportunity->title,
                            'status' => $application->status
                        ]
                    ];
                });

            // Combine and sort notifications
            $allNotifications = $dbNotifications->concat($recentApplications)
                ->sortByDesc('timestamp')
                ->values()
                ->take(20);

            return response()->json([
                'data' => $allNotifications,
                'total' => $allNotifications->count(),
                'unread_count' => $dbNotifications->where('read', false)->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch notifications: ' . $e->getMessage(),
                'data' => [],
                'total' => 0,
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();

            $notification = $user->notifications()->where('id', $id)->first();

            if ($notification) {
                $notification->markAsRead();
                return response()->json(['message' => 'Notification marked as read']);
            }

            return response()->json(['message' => 'Notification not found'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to mark notification as read: ' . $e->getMessage()
            ], 500);
        }
    }
}
