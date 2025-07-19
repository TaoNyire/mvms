<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;
use App\Models\User;
use App\Services\NotificationService;

class AnnouncementController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Verify announcement access for security and data integrity
     */
    private function verifyAnnouncementAccess(Announcement $announcement, string $action = 'access'): void
    {
        $user = Auth::user();

        // Check if announcement is published and not expired
        if ($announcement->status !== 'published' || $announcement->isExpired()) {
            \Log::warning('Access attempt to unpublished/expired announcement', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'announcement_id' => $announcement->id,
                'announcement_status' => $announcement->status,
                'is_expired' => $announcement->isExpired(),
                'action' => $action,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            abort(404, 'Announcement not found or no longer available.');
        }

        // Check if user has appropriate role for the announcement audience
        $userRole = $user->hasRole('volunteer') ? 'volunteers' : 'organizations';
        $announcementAudience = $announcement->target_audience;

        if ($announcementAudience !== 'all' && $announcementAudience !== $userRole) {
            \Log::warning('Unauthorized announcement access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $userRole,
                'announcement_id' => $announcement->id,
                'announcement_audience' => $announcementAudience,
                'action' => $action,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            abort(403, 'You are not authorized to access this announcement.');
        }
    }

    /**
     * Verify announcement ownership for modification actions
     */
    private function verifyAnnouncementOwnership(Announcement $announcement, string $action = 'modify'): void
    {
        $user = Auth::user();

        if ($announcement->created_by !== $user->id) {
            \Log::warning('Unauthorized announcement modification attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'announcement_id' => $announcement->id,
                'announcement_creator' => $announcement->created_by,
                'action' => $action,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            abort(403, 'You are not authorized to modify this announcement.');
        }
    }

    /**
     * Display announcements bulletin board with enhanced security
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Enhanced security - ensure user is properly authenticated
        if (!$user || !$user->is_active || $user->account_status !== 'active') {
            \Log::warning('Unauthorized announcements access attempt', [
                'user_id' => $user ? $user->id : null,
                'is_active' => $user ? $user->is_active : null,
                'account_status' => $user ? $user->account_status : null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to access announcements.');
        }

        // Determine user role for audience filtering
        $userRole = $user->hasRole('volunteer') ? 'volunteers' : 'organizations';

        // Secure query - only published, non-expired announcements for user's role
        $query = Announcement::published()
            ->notExpired()
            ->forAudience($userRole)
            ->with('creator')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->byType($request->type);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->byPriority($request->priority);
        }

        $announcements = $query->paginate(10);

        // Mark announcements as viewed (secure operation)
        foreach ($announcements as $announcement) {
            $announcement->markAsViewedBy($user);
        }

        // Log access for security monitoring
        \Log::info('Announcements accessed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $userRole,
            'announcements_count' => $announcements->total(),
            'filters' => [
                'type' => $request->get('type', 'all'),
                'priority' => $request->get('priority', 'all')
            ],
            'ip' => request()->ip()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['announcements' => $announcements]);
        }

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show specific announcement with enhanced security
     */
    public function show(Announcement $announcement)
    {
        // Verify announcement access for security and data integrity
        $this->verifyAnnouncementAccess($announcement, 'view_announcement');

        $user = Auth::user();

        // Mark as viewed (secure operation)
        $announcement->markAsViewedBy($user);

        // Log announcement access for security monitoring
        \Log::info('Announcement viewed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'announcement_id' => $announcement->id,
            'announcement_title' => $announcement->title,
            'announcement_type' => $announcement->type,
            'ip' => request()->ip()
        ]);

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Create new announcement (organizations only)
     */
    public function create()
    {
        // Simple version for testing
        $volunteerCount = 0;

        try {
            $volunteerCount = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'volunteer');
            })->count();
        } catch (\Exception $e) {
            \Log::warning('Could not get volunteer count: ' . $e->getMessage());
        }

        return view('announcements.create', compact('volunteerCount'));
    }

    /**
     * Store new announcement
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('organization')) {
            abort(403, 'Only organizations can create announcements.');
        }

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'type' => 'required|in:general,urgent,event,policy,system',
            'priority' => 'required|in:low,medium,high,urgent',
            'audience' => 'required|in:all,volunteers,my_volunteers,organizations,admins',
            'expires_at' => 'nullable|date|after:now',
            'is_pinned' => 'boolean',
            'is_featured' => 'boolean',
            'send_notification' => 'boolean',
            'tags' => 'nullable|array',
            'category' => 'nullable|string|max:100',
        ];

        $validatedData = $request->validate($rules);

        $announcement = Announcement::create(array_merge($validatedData, [
            'created_by' => $user->id,
            'status' => 'published',
            'published_at' => now(),
            'color' => $this->getColorForType($validatedData['type']),
            'icon' => $this->getIconForType($validatedData['type']),
        ]));

        // Send notifications if requested
        if ($validatedData['send_notification'] ?? false) {
            $this->sendAnnouncementNotifications($announcement);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'announcement' => $announcement
            ]);
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Toggle like on announcement with enhanced security
     */
    public function toggleLike(Announcement $announcement)
    {
        // Verify announcement access for security and data integrity
        $this->verifyAnnouncementAccess($announcement, 'toggle_like');

        $user = Auth::user();

        $liked = $announcement->toggleLikeBy($user);

        // Log like action for security monitoring
        \Log::info('Announcement like toggled', [
            'user_id' => $user->id,
            'announcement_id' => $announcement->id,
            'liked' => $liked,
            'action' => $liked ? 'liked' : 'unliked'
        ]);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $announcement->fresh()->likes_count
        ]);
    }

    /**
     * Pin announcement (organizations only)
     */
    public function pin(Announcement $announcement)
    {
        $user = Auth::user();

        if (!$user->hasRole('organization') && $announcement->created_by !== $user->id) {
            abort(403, 'You can only pin your own announcements.');
        }

        $announcement->pin();

        return response()->json([
            'success' => true,
            'message' => 'Announcement pinned successfully.'
        ]);
    }

    /**
     * Unpin announcement
     */
    public function unpin(Announcement $announcement)
    {
        $user = Auth::user();

        if (!$user->hasRole('organization') && $announcement->created_by !== $user->id) {
            abort(403, 'You can only unpin your own announcements.');
        }

        $announcement->unpin();

        return response()->json([
            'success' => true,
            'message' => 'Announcement unpinned successfully.'
        ]);
    }

    /**
     * Get color for announcement type
     */
    protected function getColorForType(string $type): string
    {
        return match($type) {
            'urgent' => '#dc3545',
            'event' => '#28a745',
            'policy' => '#fd7e14',
            'system' => '#6c757d',
            'general' => '#007bff',
            default => '#007bff'
        };
    }

    /**
     * Get icon for announcement type
     */
    protected function getIconForType(string $type): string
    {
        return match($type) {
            'urgent' => 'bi-exclamation-triangle',
            'event' => 'bi-calendar-event',
            'policy' => 'bi-shield-check',
            'system' => 'bi-gear',
            'general' => 'bi-megaphone',
            default => 'bi-megaphone'
        };
    }

    /**
     * Send notifications for new announcement
     */
    protected function sendAnnouncementNotifications(Announcement $announcement)
    {
        $targetUsers = $announcement->getTargetUsers();

        foreach ($targetUsers as $user) {
            $this->notificationService->createNotification($user, [
                'type' => 'announcement',
                'title' => 'New Announcement: ' . $announcement->title,
                'message' => $announcement->excerpt,
                'priority' => $announcement->priority,
                'related_type' => 'announcement',
                'related_id' => $announcement->id,
                'related_user_id' => $announcement->created_by,
                'action_url' => route('announcements.show', $announcement),
                'action_text' => 'View Announcement',
                'icon' => $announcement->icon,
                'color' => $announcement->color,
            ]);
        }
    }
}
