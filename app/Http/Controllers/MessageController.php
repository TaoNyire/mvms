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
     * Display user's conversations
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $conversations = Conversation::forUser($user)
            ->active()
            ->with(['lastMessage', 'lastActivityUser'])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json(['conversations' => $conversations]);
        }

        return view('messages.index', compact('conversations'));
    }

    /**
     * Show specific conversation
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        // Mark messages as read
        $conversation->markAsReadForUser($user);

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
        $user = Auth::user();

        $rules = [
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|min:1|max:1000',
            'title' => 'nullable|string|max:255',
        ];

        $validatedData = $request->validate($rules);

        $recipient = User::findOrFail($validatedData['recipient_id']);

        // Create or get existing direct conversation
        $conversation = Conversation::createDirect($user, $recipient);

        // Send message
        $message = $conversation->sendMessage($user, $validatedData['message']);

        // Send notification to recipient
        $this->notificationService->notifyNewMessage($recipient, $user, $validatedData['message']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'conversation' => $conversation,
                'message' => $message
            ]);
        }

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Message sent successfully!');
    }

    /**
     * Send message to existing conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $rules = [
            'message' => 'required|string|min:1|max:1000',
            'reply_to_id' => 'nullable|exists:messages,id',
        ];

        $validatedData = $request->validate($rules);

        // Send message
        $message = $conversation->sendMessage($user, $validatedData['message'], [
            'reply_to_id' => $validatedData['reply_to_id'] ?? null,
        ]);

        // Notify other participants
        $participants = $conversation->participants()->where('id', '!=', $user->id);
        foreach ($participants as $participant) {
            $this->notificationService->notifyNewMessage($participant, $user, $validatedData['message'], $conversation);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('sender')
            ]);
        }

        return back()->with('success', 'Message sent!');
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
     * Get unread message count
     */
    public function unreadCount()
    {
        $user = Auth::user();

        $count = Conversation::forUser($user)
            ->active()
            ->get()
            ->sum('unread_count');

        return response()->json(['count' => $count]);
    }
}
