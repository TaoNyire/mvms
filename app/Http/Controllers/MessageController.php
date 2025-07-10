<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Get all conversations for the authenticated user
     */
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        // Get all messages where user is either sender or receiver
        $messages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by conversation partner
        $conversations = [];
        $seen = [];

        foreach ($messages as $message) {
            $partnerId = $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;

            if (!in_array($partnerId, $seen)) {
                $seen[] = $partnerId;
                $partner = $message->sender_id == $userId ? $message->receiver : $message->sender;

                // Count unread messages from this partner
                $unreadCount = Message::where('sender_id', $partnerId)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $conversations[] = [
                    'partner' => [
                        'id' => $partner->id,
                        'name' => $partner->name,
                        'email' => $partner->email,
                        'role' => $partner->roles->first()->name ?? 'user'
                    ],
                    'last_message' => [
                        'id' => $message->id,
                        'message' => $message->message,
                        'subject' => $message->subject,
                        'created_at' => $message->created_at,
                        'sender_id' => $message->sender_id,
                        'is_from_me' => $message->sender_id == $userId
                    ],
                    'unread_count' => $unreadCount,
                    'last_message_at' => $message->created_at
                ];
            }
        }

        return response()->json([
            'conversations' => $conversations
        ]);
    }

    /**
     * Get messages between authenticated user and another user
     */
    public function getMessages(Request $request, $partnerId)
    {
        $userId = $request->user()->id;
        
        $messages = Message::betweenUsers($userId, $partnerId)
            ->with(['sender', 'receiver', 'application'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        // Mark messages as read
        Message::where('sender_id', $partnerId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json($messages);
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'application_id' => 'nullable|exists:applications,id',
            'message_type' => 'nullable|in:general,application_related,task_update,feedback_request'
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'application_id' => $request->application_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'message_type' => $request->message_type ?? 'general'
        ]);

        $message->load(['sender', 'receiver', 'application']);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }

    /**
     * Get unread message count
     */
    public function unreadCount(Request $request)
    {
        $count = Message::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Mark specific message as read
     */
    public function markAsRead(Request $request, Message $message)
    {
        // Ensure user can only mark their own received messages as read
        if ($message->receiver_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->markAsRead();

        return response()->json(['message' => 'Message marked as read']);
    }

    /**
     * Get contacts (users that can be messaged)
     */
    public function getContacts(Request $request)
    {
        $currentUser = $request->user();
        $currentUserRole = $currentUser->roles->first()->name ?? 'user';

        // Organizations can message volunteers who applied to their opportunities
        if ($currentUserRole === 'organization') {
            $contacts = User::whereHas('roles', function($query) {
                $query->where('name', 'volunteer');
            })
            ->whereHas('applications.opportunity', function($query) use ($currentUser) {
                $query->where('organization_id', $currentUser->id);
            })
            ->with('volunteerProfile')
            ->distinct()
            ->get();
        }
        // Volunteers can message organizations they applied to
        elseif ($currentUserRole === 'volunteer') {
            $contacts = User::whereHas('roles', function($query) {
                $query->where('name', 'organization');
            })
            ->whereHas('opportunities.applications', function($query) use ($currentUser) {
                $query->where('volunteer_id', $currentUser->id);
            })
            ->with('organizationProfile')
            ->distinct()
            ->get();
        }
        // Admins can message everyone
        elseif ($currentUserRole === 'admin') {
            $contacts = User::where('id', '!=', $currentUser->id)
                ->with(['volunteerProfile', 'organizationProfile', 'roles'])
                ->get();
        }
        else {
            $contacts = collect();
        }

        return response()->json(['contacts' => $contacts]);
    }
}
