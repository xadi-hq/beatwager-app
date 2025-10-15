<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GroupEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'created_by_user_id',
        'name',
        'description',
        'event_date',
        'location',
        'attendance_bonus',
        'rsvp_deadline',
        'auto_prompt_hours_after',
        'status',
        'cancelled_at',
        'cancelled_by_user_id',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'rsvp_deadline' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_prompt_hours_after' => 'integer',
        'attendance_bonus' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(GroupEventRsvp::class, 'event_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(GroupEventAttendance::class, 'event_id');
    }

    /**
     * Get all transactions for this event (attendance bonuses)
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function isUpcoming(): bool
    {
        return $this->event_date->isFuture() && $this->status === 'upcoming';
    }

    public function isPast(): bool
    {
        return $this->event_date->isPast();
    }

    public function isProcessed(): bool
    {
        return $this->status === 'completed';
    }

    public function needsAttendancePrompt(): bool
    {
        if ($this->status !== 'upcoming') {
            return false;
        }

        // Don't send if already prompted
        if ($this->attendance_prompt_sent_at) {
            return false;
        }

        $promptTime = $this->event_date->copy()->addHours($this->auto_prompt_hours_after);
        return now()->greaterThanOrEqualTo($promptTime) && $this->attendance()->count() === 0;
    }

    /**
     * Check if event needs RSVP reminder (24h before deadline or event)
     */
    public function needsRsvpReminder(): bool
    {
        if ($this->status !== 'upcoming') {
            return false;
        }

        // Don't send if already reminded
        if ($this->rsvp_reminder_sent_at) {
            return false;
        }

        // Determine trigger time: 24h before RSVP deadline OR 24h before event
        if ($this->rsvp_deadline) {
            $triggerTime = $this->rsvp_deadline->copy()->subHours(24);
        } else {
            $triggerTime = $this->event_date->copy()->subHours(24);
        }

        return now()->greaterThanOrEqualTo($triggerTime);
    }

    /**
     * Check if event should be deleted (no RSVPs and deadline passed)
     */
    public function shouldBeDeleted(): bool
    {
        $hasNoRsvps = $this->rsvps()->count() === 0;
        $deadlinePassed = ($this->rsvp_deadline && $this->rsvp_deadline->isPast())
                       || $this->event_date->isPast();

        return $hasNoRsvps && $deadlinePassed;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'completed')
              ->orWhere(function ($subQ) {
                  $subQ->where('status', 'upcoming')
                       ->where(function ($deadlineQ) {
                           $deadlineQ->whereHas('rsvps')
                                    ->orWhere(function ($dateQ) {
                                        $dateQ->where(function ($rsvpQ) {
                                            $rsvpQ->whereNull('rsvp_deadline')
                                                  ->orWhere('rsvp_deadline', '>=', now());
                                        })->where('event_date', '>=', now());
                                    });
                       });
              });
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'upcoming')
                    ->whereDoesntHave('rsvps')
                    ->where(function ($q) {
                        $q->where('rsvp_deadline', '<', now())
                          ->orWhere('event_date', '<', now());
                    });
    }
}
