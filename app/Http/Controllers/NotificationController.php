<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\UserNotificationPreference;

class NotificationController extends Controller
{
    /**
     * Display user's notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::where('user_id', $user->id)
            ->notExpired()
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'unread':
                    $query->unread();
                    break;
                case 'read':
                    $query->read();
                    break;
                case 'archived':
                    $query->archived();
                    break;
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $notifications = $query->paginate(20);

        // Get counts for tabs
        $counts = [
            'all' => Notification::where('user_id', $user->id)->notExpired()->count(),
            'unread' => Notification::where('user_id', $user->id)->unread()->notExpired()->count(),
            'read' => Notification::where('user_id', $user->id)->read()->notExpired()->count(),
            'archived' => Notification::where('user_id', $user->id)->archived()->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'counts' => $counts
            ]);
        }

        return view('notifications.index', compact('notifications', 'counts'));
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->unread()
            ->notExpired()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.'
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification)
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread.'
        ]);
    }

    /**
     * Archive notification
     */
    public function archive(Notification $notification)
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->archive();

        return response()->json([
            'success' => true,
            'message' => 'Notification archived.'
        ]);
    }

    /**
     * Unarchive notification
     */
    public function unarchive(Notification $notification)
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->unarchive();

        return response()->json([
            'success' => true,
            'message' => 'Notification unarchived.'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->unread()
            ->update([
                'status' => 'read',
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        // Check if user owns this notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.'
        ]);
    }

    /**
     * Bulk actions on notifications
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'action' => 'required|in:read,unread,archive,delete',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ];

        $validatedData = $request->validate($rules);

        $notifications = Notification::where('user_id', $user->id)
            ->whereIn('id', $validatedData['notification_ids'])
            ->get();

        $count = 0;
        foreach ($notifications as $notification) {
            switch ($validatedData['action']) {
                case 'read':
                    $notification->markAsRead();
                    $count++;
                    break;
                case 'unread':
                    $notification->markAsUnread();
                    $count++;
                    break;
                case 'archive':
                    $notification->archive();
                    $count++;
                    break;
                case 'delete':
                    $notification->delete();
                    $count++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications updated."
        ]);
    }

    /**
     * Show notification preferences
     */
    public function preferences()
    {
        $user = Auth::user();
        $preferences = $user->notificationPreferences;

        if (!$preferences) {
            $preferences = UserNotificationPreference::createForUser($user);
        }

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        $preferences = $user->notificationPreferences;

        if (!$preferences) {
            $preferences = UserNotificationPreference::createForUser($user);
        }

        $rules = [
            'notifications_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'in_app_task_assigned' => 'boolean',
            'in_app_task_updated' => 'boolean',
            'in_app_application_status' => 'boolean',
            'in_app_messages' => 'boolean',
            'in_app_announcements' => 'boolean',
            'in_app_reminders' => 'boolean',
            'in_app_schedule_changes' => 'boolean',
            'email_task_assigned' => 'boolean',
            'email_task_updated' => 'boolean',
            'email_application_status' => 'boolean',
            'email_messages' => 'boolean',
            'email_announcements' => 'boolean',
            'email_reminders' => 'boolean',
            'email_schedule_changes' => 'boolean',
            'email_weekly_digest' => 'boolean',
            'sms_urgent_only' => 'boolean',
            'sms_task_assigned' => 'boolean',
            'sms_application_status' => 'boolean',
            'sms_reminders' => 'boolean',
            'sms_schedule_changes' => 'boolean',
            'quiet_hours_start' => 'date_format:H:i',
            'quiet_hours_end' => 'date_format:H:i',
            'respect_quiet_hours' => 'boolean',
            'notification_days' => 'array',
            'digest_frequency' => 'in:none,daily,weekly,monthly',
            'reminder_frequency' => 'in:none,once,daily,hourly',
            'max_notifications_per_day' => 'integer|min:1|max:100',
            'group_similar_notifications' => 'boolean',
            'notification_retention_days' => 'integer|min:1|max:365',
            'auto_mark_read_after_days' => 'boolean',
            'auto_mark_read_days' => 'integer|min:1|max:30',
            'preferred_email' => 'nullable|email',
            'preferred_phone' => 'nullable|string',
            'timezone' => 'string',
            'language' => 'string',
        ];

        $validatedData = $request->validate($rules);

        // Convert checkboxes to boolean values
        $booleanFields = [
            'notifications_enabled', 'email_notifications', 'sms_notifications', 'push_notifications',
            'in_app_task_assigned', 'in_app_task_updated', 'in_app_application_status', 'in_app_messages',
            'in_app_announcements', 'in_app_reminders', 'in_app_schedule_changes',
            'email_task_assigned', 'email_task_updated', 'email_application_status', 'email_messages',
            'email_announcements', 'email_reminders', 'email_schedule_changes', 'email_weekly_digest',
            'sms_urgent_only', 'sms_task_assigned', 'sms_application_status', 'sms_reminders',
            'sms_schedule_changes', 'respect_quiet_hours', 'group_similar_notifications',
            'auto_mark_read_after_days'
        ];

        foreach ($booleanFields as $field) {
            $validatedData[$field] = $request->has($field);
        }

        $preferences->updatePreferences($validatedData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully!'
            ]);
        }

        return back()->with('success', 'Notification preferences updated successfully!');
    }

    /**
     * Reset preferences to defaults
     */
    public function resetPreferences()
    {
        $user = Auth::user();
        $preferences = $user->notificationPreferences;

        if (!$preferences) {
            $preferences = UserNotificationPreference::createForUser($user);
        } else {
            $preferences->resetToDefaults();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences reset to defaults.'
        ]);
    }
}
