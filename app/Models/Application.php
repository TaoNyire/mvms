<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'volunteer_id',
        'message',
        'relevant_experience',
        'availability_details',
        'agrees_to_terms',
        'status',
        'organization_notes',
        'rejection_reason',
        'feedback',
        'rating',
        'applied_at',
        'reviewed_at',
        'accepted_at',
        'rejected_at',
        'completed_at',
        'email_sent',
        'last_email_sent_at',
        'email_history',
        'custom_responses',
        'background_check_completed',
        'training_completed',
        'special_requirements',
    ];

    protected $casts = [
        'availability_details' => 'array',
        'email_history' => 'array',
        'custom_responses' => 'array',
        'agrees_to_terms' => 'boolean',
        'email_sent' => 'boolean',
        'background_check_completed' => 'boolean',
        'training_completed' => 'boolean',
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'days_since_applied',
        'can_be_withdrawn'
    ];

    /**
     * Relationship: Application belongs to an Opportunity
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Relationship: Application belongs to a Volunteer (User)
     */
    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Under Review',
            'accepted' => 'Accepted',
            'rejected' => 'Not Selected',
            'withdrawn' => 'Withdrawn',
            'completed' => 'Completed',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get days since application was submitted
     */
    public function getDaysSinceAppliedAttribute(): int
    {
        return $this->applied_at->diffInDays(now());
    }

    /**
     * Check if application can be withdrawn
     */
    public function getCanBeWithdrawnAttribute(): bool
    {
        return in_array($this->status, ['pending', 'accepted']) &&
               $this->opportunity->start_date->isFuture();
    }

    /**
     * Scope: Pending applications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Accepted applications
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: For a specific volunteer
     */
    public function scopeForVolunteer($query, $volunteerId)
    {
        return $query->where('volunteer_id', $volunteerId);
    }

    /**
     * Scope: For a specific opportunity
     */
    public function scopeForOpportunity($query, $opportunityId)
    {
        return $query->where('opportunity_id', $opportunityId);
    }

    /**
     * Accept the application
     */
    public function accept($notes = null)
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'reviewed_at' => now(),
            'organization_notes' => $notes,
        ]);

        // Update opportunity volunteers count
        $this->opportunity->updateVolunteersCount();

        // Send acceptance email
        $this->sendStatusEmail('accepted');

        return $this;
    }

    /**
     * Reject the application
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Send rejection email
        $this->sendStatusEmail('rejected');

        return $this;
    }

    /**
     * Withdraw the application
     */
    public function withdraw()
    {
        if (!$this->can_be_withdrawn) {
            throw new \Exception('Application cannot be withdrawn at this time.');
        }

        $this->update([
            'status' => 'withdrawn',
        ]);

        // Update opportunity volunteers count if was accepted
        if ($this->status === 'accepted') {
            $this->opportunity->updateVolunteersCount();
        }

        return $this;
    }

    /**
     * Mark application as completed
     */
    public function complete($feedback = null, $rating = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'feedback' => $feedback,
            'rating' => $rating,
        ]);

        return $this;
    }

    /**
     * Send status change email to volunteer
     */
    public function sendStatusEmail($status)
    {
        try {
            // This would integrate with your email system
            // For now, we'll just log it
            \Log::info("Email sent to volunteer {$this->volunteer->email} for application {$this->id} - Status: {$status}");

            $this->update([
                'email_sent' => true,
                'last_email_sent_at' => now(),
                'email_history' => array_merge($this->email_history ?? [], [
                    [
                        'status' => $status,
                        'sent_at' => now()->toISOString(),
                        'type' => 'status_change'
                    ]
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send email for application {$this->id}: " . $e->getMessage());
        }
    }
}
