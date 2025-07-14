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
     * Display announcements bulletin board
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->hasRole('volunteer') ? 'volunteers' : 'organizations';

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

        // Mark announcements as viewed
        foreach ($announcements as $announcement) {
            $announcement->markAsViewedBy($user);
        }

        if ($request->expectsJson()) {
            return response()->json(['announcements' => $announcements]);
        }

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show specific announcement
     */
    public function show(Announcement $announcement)
    {
        $user = Auth::user();

        // Check if user can view this announcement
        if (!$announcement->is_published || $announcement->is_expired) {
            abort(404, 'Announcement not found.');
        }

        // Mark as viewed
        $announcement->markAsViewedBy($user);

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Create new announcement (organizations only)
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->hasRole('organization')) {
            abort(403, 'Only organizations can create announcements.');
        }

        return view('announcements.create');
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
            'audience' => 'required|in:all,volunteers,organizations,admins',
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
     * Toggle like on announcement
     */
    public function toggleLike(Announcement $announcement)
    {
        $user = Auth::user();

        $liked = $announcement->toggleLikeBy($user);

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
