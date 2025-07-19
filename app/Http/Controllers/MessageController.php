<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;

class MessageController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Verify conversation access for security and data integrity
     */
    private function verifyConversationAccess(Conversation $conversation, string $action = 'access'): void
    {
        $user = Auth::user();

        if (!$conversation->hasParticipant($user)) {
            \Log::warning('Unauthorized conversation access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'conversation_id' => $conversation->id,
                'action' => $action,
                'participants' => $conversation->participants->pluck('id')->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            abort(403, 'You are not authorized to access this conversation.');
        }
    }

    /**
     * Verify message ownership for security and data integrity
     */
    private function verifyMessageOwnership(Message $message, string $action = 'access'): void
    {
        $user = Auth::user();

        if ($message->sender_id !== $user->id) {
            \Log::warning('Unauthorized message access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'message_id' => $message->id,
                'message_sender' => $message->sender_id,
                'action' => $action,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            abort(403, 'You are not authorized to access this message.');
        }
    }

    /**
     * Display user's conversations with enhanced security
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Enhanced security - ensure user is properly authenticated
        if (!$user || !$user->is_active || $user->account_status !== 'active') {
            \Log::warning('Unauthorized messages access attempt', [
                'user_id' => $user ? $user->id : null,
                'is_active' => $user ? $user->is_active : null,
                'account_status' => $user ? $user->account_status : null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect()->route('login')->with('error', 'Please login to access messages.');
        }

        // Secure query - only conversations where user is a participant
        $conversations = Conversation::forUser($user)
            ->active()
            ->with(['lastMessage', 'lastActivityUser'])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        // Log access for security monitoring
        \Log::info('Messages accessed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'conversations_count' => $conversations->total(),
            'ip' => request()->ip()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['conversations' => $conversations]);
        }

        return view('messages.index', compact('conversations'));
    }

    /**
     * Show specific conversation with enhanced security
     */
    public function show(Conversation $conversation)
    {
        // Verify conversation access for security and data integrity
        $this->verifyConversationAccess($conversation, 'view_conversation');

        $user = Auth::user();

        // Mark messages as read
        $conversation->markAsReadForUser($user);

        // Log conversation access for security monitoring
        \Log::info('Conversation accessed', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'action' => 'view_conversation'
        ]);

        // Load messages with pagination
        $messages = $conversation->messages()
            ->with(['sender', 'replyTo'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Get participants
        $participants = $conversation->participants();

        return view('messages.show', compact('conversation', 'messages', 'participants'));
    }

    /**
     * Start new conversation
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get potential recipients based on user role
        if ($user->hasRole('volunteer')) {
            // Volunteers can message organizations they've applied to or been assigned by
            $recipients = User::whereHas('roles', function($query) {
                $query->where('name', 'organization');
            })->get();
        } else {
            // Organizations can message volunteers who have applied or been assigned
            $recipients = User::whereHas('roles', function($query) {
                $query->where('name', 'volunteer');
            })->get();
        }

        return view('messages.create', compact('recipients'));
    }

    /**
     * Store new conversation
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $rules = [
                'recipients' => 'required|array|min:1',
                'recipients.*' => 'required|exists:users,id',
                'content' => 'required|string|min:1|max:1000',
                'subject' => 'required|string|max:255',
            ];

            $validatedData = $request->validate($rules);

            $recipients = User::whereIn('id', $validatedData['recipients'])->get();
            $conversations = [];
            $messages = [];

            // Create separate conversations for each recipient
            foreach ($recipients as $recipient) {
                // Create or get existing direct conversation
                $conversation = Conversation::createDirect($user, $recipient);

                // Send message
                $message = $conversation->sendMessage($user, $validatedData['content']);

                // Send notification to recipient (with error handling)
                try {
                    $this->notificationService->notifyNewMessage($recipient, $user, $validatedData['content']);
                } catch (\Exception $notificationError) {
                    // Log notification error but don't fail the message sending
                    \Log::warning('Failed to send notification for new message', [
                        'error' => $notificationError->getMessage(),
                        'message_id' => $message->id,
                        'recipient_id' => $recipient->id,
                        'sender_id' => $user->id
                    ]);
                }

                $conversations[] = $conversation;
                $messages[] = $message;
            }

            // Log successful message creation
            \Log::info('New conversation and message created successfully', [
                'user_id' => $user->id,
                'user_role' => $user->roles->pluck('name')->first(),
                'recipients_count' => count($recipients),
                'conversations_count' => count($conversations)
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'conversations' => $conversations,
                    'messages' => $messages
                ]);
            }

            // If only one recipient, redirect to that conversation
            if (count($conversations) === 1) {
                return redirect()->route('messages.show', $conversations[0])
                    ->with('success', 'Message sent successfully!');
            }

            // Multiple recipients, redirect to messages index
            return redirect()->route('messages.index')
                ->with('success', 'Messages sent successfully to ' . count($recipients) . ' recipients!');

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error creating new conversation/message', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'user_role' => Auth::user() ? Auth::user()->roles->pluck('name')->first() : 'unknown',
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error sending message. Please try again.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Error sending message. Please try again.'])->withInput();
        }
    }

    /**
     * Send message to existing conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        try {
            // Verify conversation access for security and data integrity
            $this->verifyConversationAccess($conversation, 'send_message');

            $user = Auth::user();

            $rules = [
                'content' => 'required|string|min:1|max:1000',
                'message' => 'nullable|string|min:1|max:1000', // Support both field names for compatibility
                'reply_to_id' => 'nullable|exists:messages,id',
            ];

            $validatedData = $request->validate($rules);

            // Use 'content' field first, fallback to 'message' for compatibility
            $messageContent = $validatedData['content'] ?? $validatedData['message'] ?? '';

            if (empty($messageContent)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Message content is required.'], 422);
                }
                return back()->withErrors(['content' => 'Message content is required.']);
            }

            // Send message
            $message = $conversation->sendMessage($user, $messageContent, [
                'reply_to_id' => $validatedData['reply_to_id'] ?? null,
            ]);

            // Notify other participants (with error handling)
            try {
                $participants = $conversation->participants()->filter(function($participant) use ($user) {
                    return $participant->id !== $user->id;
                });
                foreach ($participants as $participant) {
                    $this->notificationService->notifyNewMessage($participant, $user, $messageContent, $conversation);
                }
            } catch (\Exception $notificationError) {
                // Log notification error but don't fail the message sending
                \Log::warning('Failed to send notification for new message', [
                    'error' => $notificationError->getMessage(),
                    'message_id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id
                ]);
            }

            // Log successful message sending
            \Log::info('Message sent successfully', [
                'user_id' => $user->id,
                'user_role' => $user->roles->pluck('name')->first(),
                'conversation_id' => $conversation->id,
                'message_id' => $message->id
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message->load('sender')
                ]);
            }

            return back()->with('success', 'Message sent!');

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error sending message', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'user_role' => Auth::user() ? Auth::user()->roles->pluck('name')->first() : 'unknown',
                'conversation_id' => $conversation->id,
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error sending message. Please try again.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Error sending message. Please try again.']);
        }
    }

    /**
     * React to message
     */
    public function react(Request $request, Message $message)
    {
        $user = Auth::user();

        // Check if user is participant in conversation
        if (!$message->conversation->hasParticipant($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $rules = [
            'emoji' => 'required|string|max:10',
        ];

        $validatedData = $request->validate($rules);

        $message->addReaction($user, $validatedData['emoji']);

        return response()->json([
            'success' => true,
            'reactions' => $message->fresh()->reaction_counts
        ]);
    }

    /**
     * Remove reaction from message
     */
    public function removeReaction(Message $message)
    {
        $user = Auth::user();

        // Check if user is participant in conversation
        if (!$message->conversation->hasParticipant($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $message->removeReaction($user);

        return response()->json([
            'success' => true,
            'reactions' => $message->fresh()->reaction_counts
        ]);
    }

    /**
     * Archive conversation
     */
    public function archive(Conversation $conversation)
    {
        $user = Auth::user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $conversation->archive();

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived.'
        ]);
    }

    /**
     * Get unread message count with enhanced security
     */
    public function unreadCount()
    {
        try {
            $user = Auth::user();

            // Enhanced security - ensure user is properly authenticated
            if (!$user || !$user->is_active || $user->account_status !== 'active') {
                \Log::warning('Unauthorized message count access attempt', [
                    'user_id' => $user ? $user->id : null,
                    'is_active' => $user ? $user->is_active : null,
                    'account_status' => $user ? $user->account_status : null,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return response()->json(['count' => 0], 401);
            }

            // Secure query - only conversations where user is a participant
            $count = Conversation::forUser($user)
                ->active()
                ->get()
                ->sum('unread_count');

            // Log for security monitoring (only in debug mode to avoid log spam)
            if (config('app.debug')) {
                \Log::debug('Message count accessed', [
                    'user_id' => $user->id,
                    'count' => $count
                ]);
            }

            return response()->json(['count' => $count]);

        } catch (\Exception $e) {
            \Log::error('Failed to get message count', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return response()->json(['count' => 0], 500);
        }
    }
}
