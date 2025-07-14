<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'created_by',
        'participant_ids',
        'participant_count',
        'related_type',
        'related_id',
        'status',
        'is_private',
        'allow_file_sharing',
        'notifications_enabled',
        'last_message_id',
        'last_activity_at',
        'last_activity_by',
        'color',
        'icon',
        'settings',
    ];

    protected $casts = [
        'participant_ids' => 'array',
        'settings' => 'array',
        'last_activity_at' => 'datetime',
        'is_private' => 'boolean',
        'allow_file_sharing' => 'boolean',
        'notifications_enabled' => 'boolean',
    ];

    protected $appends = [
        'unread_count',
        'last_message_preview',
    ];

    /**
     * Relationship: Conversation belongs to a Creator (User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Conversation has many Messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Relationship: Conversation belongs to Last Message
     */
    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    /**
     * Relationship: Conversation belongs to Last Activity User
     */
    public function lastActivityUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_activity_by');
    }

    /**
     * Get participants as User models
     */
    public function participants()
    {
        if (!$this->participant_ids) {
            return collect();
        }

        return User::whereIn('id', $this->participant_ids)->get();
    }

    /**
     * Get unread message count for current user
     */
    public function getUnreadCountAttribute(): int
    {
        $userId = Auth::id();
        if (!$userId) {
            return 0;
        }

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereJsonDoesntContain('read_by', $userId)
            ->count();
    }

    /**
     * Get last message preview
     */
    public function getLastMessagePreviewAttribute(): ?string
    {
        if (!$this->lastMessage) {
            return null;
        }

        $content = $this->lastMessage->content;

        if ($this->lastMessage->type === 'system') {
            return $content;
        }

        return Str::limit($content, 100);
    }

    /**
     * Scope: Active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Conversations for user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->whereJsonContains('participant_ids', $user->id);
    }

    /**
     * Scope: Direct conversations
     */
    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    /**
     * Scope: Group conversations
     */
    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    /**
     * Check if user is participant
     */
    public function hasParticipant(User $user): bool
    {
        return in_array($user->id, $this->participant_ids ?? []);
    }

    /**
     * Add participant to conversation
     */
    public function addParticipant(User $user): void
    {
        if ($this->hasParticipant($user)) {
            return;
        }

        $participants = $this->participant_ids ?? [];
        $participants[] = $user->id;

        $this->update([
            'participant_ids' => $participants,
            'participant_count' => count($participants),
        ]);

        // Create system message
        Message::createSystemMessage($this, 'user_added', [
            'added_user' => $user->name,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Remove participant from conversation
     */
    public function removeParticipant(User $user): void
    {
        $participants = $this->participant_ids ?? [];
        $participants = array_filter($participants, fn($id) => $id !== $user->id);

        $this->update([
            'participant_ids' => array_values($participants),
            'participant_count' => count($participants),
        ]);

        // Create system message
        Message::createSystemMessage($this, 'user_removed', [
            'removed_user' => $user->name,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Send message to conversation
     */
    public function sendMessage(User $sender, string $content, array $options = []): Message
    {
        $message = Message::create(array_merge([
            'conversation_id' => $this->id,
            'sender_id' => $sender->id,
            'content' => $content,
            'type' => 'text',
            'status' => 'sent',
        ], $options));

        // Update conversation last activity
        $this->update([
            'last_message_id' => $message->id,
            'last_activity_at' => now(),
            'last_activity_by' => $sender->id,
        ]);

        return $message;
    }

    /**
     * Mark all messages as read for user
     */
    public function markAsReadForUser(User $user): void
    {
        $messages = $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereJsonDoesntContain('read_by', $user->id)
            ->get();

        foreach ($messages as $message) {
            $message->markAsReadBy($user);
        }
    }

    /**
     * Create direct conversation between two users
     */
    public static function createDirect(User $user1, User $user2, $relatedModel = null): self
    {
        // Check if conversation already exists
        $existing = static::where('type', 'direct')
            ->where('participant_count', 2)
            ->whereJsonContains('participant_ids', $user1->id)
            ->whereJsonContains('participant_ids', $user2->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return static::create([
            'type' => 'direct',
            'created_by' => $user1->id,
            'participant_ids' => [$user1->id, $user2->id],
            'participant_count' => 2,
            'related_type' => $relatedModel ? class_basename($relatedModel) : null,
            'related_id' => $relatedModel ? $relatedModel->id : null,
            'status' => 'active',
            'is_private' => true,
            'allow_file_sharing' => true,
            'notifications_enabled' => true,
        ]);
    }

    /**
     * Create group conversation
     */
    public static function createGroup(User $creator, array $participantIds, string $title, array $options = []): self
    {
        $participantIds[] = $creator->id;
        $participantIds = array_unique($participantIds);

        return static::create(array_merge([
            'title' => $title,
            'type' => 'group',
            'created_by' => $creator->id,
            'participant_ids' => $participantIds,
            'participant_count' => count($participantIds),
            'status' => 'active',
            'is_private' => true,
            'allow_file_sharing' => true,
            'notifications_enabled' => true,
        ], $options));
    }

    /**
     * Archive conversation
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Close conversation
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Reopen conversation
     */
    public function reopen(): void
    {
        $this->update(['status' => 'active']);
    }
}
