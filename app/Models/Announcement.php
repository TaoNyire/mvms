<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'content',
        'type',
        'priority',
        'audience',
        'target_user_ids',
        'target_roles',
        'target_locations',
        'published_at',
        'expires_at',
        'is_scheduled',
        'auto_expire',
        'status',
        'is_pinned',
        'is_featured',
        'allow_comments',
        'send_notification',
        'views_count',
        'likes_count',
        'comments_count',
        'viewed_by',
        'liked_by',
        'featured_image',
        'attachments',
        'external_link',
        'external_link_text',
        'tags',
        'category',
        'color',
        'icon',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'target_user_ids' => 'array',
        'target_roles' => 'array',
        'target_locations' => 'array',
        'viewed_by' => 'array',
        'liked_by' => 'array',
        'attachments' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_scheduled' => 'boolean',
        'auto_expire' => 'boolean',
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'send_notification' => 'boolean',
    ];

    protected $appends = [
        'is_published',
        'is_expired',
        'time_ago',
        'excerpt',
    ];

    /**
     * Relationship: Announcement belongs to a Creator (User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get related model (polymorphic)
     */
    public function related()
    {
        if ($this->related_type && $this->related_id) {
            $modelClass = 'App\\Models\\' . ucfirst($this->related_type);
            if (class_exists($modelClass)) {
                return $modelClass::find($this->related_id);
            }
        }
        return null;
    }

    /**
     * Check if announcement is published
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' &&
               ($this->published_at === null || $this->published_at <= now());
    }

    /**
     * Check if announcement is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Get time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get content excerpt
     */
    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Scope: Published announcements
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where(function($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    /**
     * Scope: Not expired announcements
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope: For specific audience
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function($q) use ($audience) {
            $q->where('audience', 'all')
              ->orWhere('audience', $audience);
        });
    }

    /**
     * Scope: Pinned announcements
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope: Featured announcements
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Check if user has viewed announcement
     */
    public function hasBeenViewedBy(User $user): bool
    {
        return in_array($user->id, $this->viewed_by ?? []);
    }

    /**
     * Check if user has liked announcement
     */
    public function hasBeenLikedBy(User $user): bool
    {
        return in_array($user->id, $this->liked_by ?? []);
    }

    /**
     * Mark as viewed by user
     */
    public function markAsViewedBy(User $user): void
    {
        if (!$this->hasBeenViewedBy($user)) {
            $viewedBy = $this->viewed_by ?? [];
            $viewedBy[] = $user->id;

            $this->update([
                'viewed_by' => $viewedBy,
                'views_count' => $this->views_count + 1,
            ]);
        }
    }

    /**
     * Toggle like by user
     */
    public function toggleLikeBy(User $user): bool
    {
        $likedBy = $this->liked_by ?? [];

        if ($this->hasBeenLikedBy($user)) {
            // Remove like
            $likedBy = array_filter($likedBy, fn($id) => $id !== $user->id);
            $this->update([
                'liked_by' => array_values($likedBy),
                'likes_count' => max(0, $this->likes_count - 1),
            ]);
            return false;
        } else {
            // Add like
            $likedBy[] = $user->id;
            $this->update([
                'liked_by' => $likedBy,
                'likes_count' => $this->likes_count + 1,
            ]);
            return true;
        }
    }

    /**
     * Publish announcement
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Archive announcement
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Pin announcement
     */
    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin announcement
     */
    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    /**
     * Feature announcement
     */
    public function feature(): void
    {
        $this->update(['is_featured' => true]);
    }

    /**
     * Unfeature announcement
     */
    public function unfeature(): void
    {
        $this->update(['is_featured' => false]);
    }

    /**
     * Get target users for this announcement
     */
    public function getTargetUsers()
    {
        $query = User::query();

        switch ($this->audience) {
            case 'volunteers':
                $query->whereHas('roles', fn($q) => $q->where('name', 'volunteer'));
                break;
            case 'organizations':
                $query->whereHas('roles', fn($q) => $q->where('name', 'organization'));
                break;
            case 'admins':
                $query->whereHas('roles', fn($q) => $q->where('name', 'admin'));
                break;
            case 'custom':
                if ($this->target_user_ids) {
                    $query->whereIn('id', $this->target_user_ids);
                }
                break;
            case 'all':
            default:
                // No additional filtering
                break;
        }

        // Apply role filtering if specified
        if ($this->target_roles) {
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $this->target_roles));
        }

        // Apply location filtering if specified
        if ($this->target_locations) {
            $query->where(function($q) {
                foreach ($this->target_locations as $location) {
                    $q->orWhere('district', $location)
                      ->orWhere('region', $location);
                }
            });
        }

        return $query->get();
    }
}
