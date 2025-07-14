<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'attachments',
        'metadata',
        'reply_to_id',
        'thread_depth',
        'status',
        'delivered_at',
        'read_by',
        'edited_at',
        'is_edited',
        'is_deleted',
        'priority',
        'is_important',
        'requires_response',
        'response_deadline',
        'reactions',
        'is_pinned',
        'pinned_at',
        'pinned_by',
        'system_action',
        'system_data',
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'read_by' => 'array',
        'reactions' => 'array',
        'system_data' => 'array',
        'delivered_at' => 'datetime',
        'edited_at' => 'datetime',
        'response_deadline' => 'datetime',
        'pinned_at' => 'datetime',
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean',
        'is_important' => 'boolean',
        'requires_response' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    protected $appends = [
        'time_ago',
        'is_read_by_user',
        'reaction_counts',
    ];

    /**
     * Relationship: Message belongs to a Conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Relationship: Message belongs to a Sender (User)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship: Message belongs to a Reply (Message)
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Relationship: Message belongs to a Pinner (User)
     */
    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    /**
     * Get time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if message is read by current user
     */
    public function getIsReadByUserAttribute(): bool
    {
        $userId = Auth::id();
        if (!$userId || !$this->read_by) {
            return false;
        }

        return isset($this->read_by[$userId]);
    }

    /**
     * Get reaction counts
     */
    public function getReactionCountsAttribute(): array
    {
        if (!$this->reactions) {
            return [];
        }

        $counts = [];
        foreach ($this->reactions as $reaction) {
            $emoji = $reaction['emoji'] ?? 'like';
            $counts[$emoji] = ($counts[$emoji] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Scope: Not deleted messages
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope: System messages
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope: Important messages
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope: Pinned messages
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Mark message as read by user
     */
    public function markAsReadBy(User $user): void
    {
        $readBy = $this->read_by ?? [];
        $readBy[$user->id] = now()->toDateTimeString();

        $this->update(['read_by' => $readBy]);
    }

    /**
     * Add reaction to message
     */
    public function addReaction(User $user, string $emoji): void
    {
        $reactions = $this->reactions ?? [];

        // Remove existing reaction from this user
        $reactions = array_filter($reactions, function($reaction) use ($user) {
            return $reaction['user_id'] !== $user->id;
        });

        // Add new reaction
        $reactions[] = [
            'user_id' => $user->id,
            'emoji' => $emoji,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->update(['reactions' => array_values($reactions)]);
    }

    /**
     * Remove reaction from message
     */
    public function removeReaction(User $user): void
    {
        $reactions = $this->reactions ?? [];

        $reactions = array_filter($reactions, function($reaction) use ($user) {
            return $reaction['user_id'] !== $user->id;
        });

        $this->update(['reactions' => array_values($reactions)]);
    }

    /**
     * Pin message
     */
    public function pin(User $user): void
    {
        $this->update([
            'is_pinned' => true,
            'pinned_at' => now(),
            'pinned_by' => $user->id,
        ]);
    }

    /**
     * Unpin message
     */
    public function unpin(): void
    {
        $this->update([
            'is_pinned' => false,
            'pinned_at' => null,
            'pinned_by' => null,
        ]);
    }

    /**
     * Edit message content
     */
    public function editContent(string $newContent): void
    {
        $this->update([
            'content' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Soft delete message
     */
    public function softDeleteMessage(): void
    {
        $this->update([
            'is_deleted' => true,
            'content' => '[Message deleted]',
        ]);
    }

    /**
     * Create system message
     */
    public static function createSystemMessage(Conversation $conversation, string $action, array $data = []): self
    {
        return static::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->created_by,
            'content' => static::getSystemMessageContent($action, $data),
            'type' => 'system',
            'system_action' => $action,
            'system_data' => $data,
            'status' => 'sent',
        ]);
    }

    /**
     * Get system message content
     */
    protected static function getSystemMessageContent(string $action, array $data): string
    {
        return match($action) {
            'user_joined' => ($data['user_name'] ?? 'Someone') . ' joined the conversation',
            'user_left' => ($data['user_name'] ?? 'Someone') . ' left the conversation',
            'conversation_created' => 'Conversation created',
            'title_changed' => 'Conversation title changed to: ' . ($data['new_title'] ?? ''),
            'user_added' => ($data['added_user'] ?? 'Someone') . ' was added to the conversation',
            'user_removed' => ($data['removed_user'] ?? 'Someone') . ' was removed from the conversation',
            default => 'System action: ' . $action
        };
    }
}
