<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledMessage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'message_type',
        'title',
        'scheduled_date',
        'message_template',
        'llm_instructions',
        'is_recurring',
        'recurrence_type',
        'is_active',
        'last_sent_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    /**
     * Get the group that owns this scheduled message
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Check if this message should be sent today
     */
    public function shouldSendToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        // For one-time messages
        if (!$this->is_recurring) {
            return $this->scheduled_date->toDateString() === $today
                && $this->last_sent_at === null;
        }

        // For recurring messages
        return $this->matchesRecurrence($today)
            && ($this->last_sent_at === null || !$this->last_sent_at->isToday());
    }

    /**
     * Check if today matches the recurrence pattern
     */
    private function matchesRecurrence(string $today): bool
    {
        $scheduledDate = $this->scheduled_date;
        $todayDate = \Carbon\Carbon::parse($today);

        return match($this->recurrence_type) {
            'daily' => true,
            'weekly' => $todayDate->dayOfWeek === $scheduledDate->dayOfWeek,
            'monthly' => $todayDate->day === $scheduledDate->day,
            'yearly' => $todayDate->month === $scheduledDate->month
                && $todayDate->day === $scheduledDate->day,
            default => false,
        };
    }

    /**
     * Get the next occurrence date for this message
     */
    public function getNextOccurrence(): ?\Carbon\Carbon
    {
        if (!$this->is_active) {
            return null;
        }

        if (!$this->is_recurring) {
            return $this->scheduled_date->isFuture() ? $this->scheduled_date : null;
        }

        $base = $this->scheduled_date->copy();
        $now = now();

        return match($this->recurrence_type) {
            'daily' => $now->copy()->addDay()->startOfDay(),
            'weekly' => $now->copy()->next($base->dayOfWeek)->startOfDay(),
            'monthly' => $now->copy()->day($base->day)->startOfDay(),
            'yearly' => $now->copy()->month($base->month)->day($base->day)->startOfDay(),
            default => null,
        };
    }
}
