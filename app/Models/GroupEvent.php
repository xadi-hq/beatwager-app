<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'rsvp_deadline' => 'datetime',
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

    public function rsvps(): HasMany
    {
        return $this->hasMany(GroupEventRsvp::class, 'event_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(GroupEventAttendance::class, 'event_id');
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

        $promptTime = $this->event_date->copy()->addHours($this->auto_prompt_hours_after);
        return now()->greaterThanOrEqualTo($promptTime) && $this->attendance()->count() === 0;
    }
}
