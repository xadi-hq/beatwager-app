<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupEventAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'group_event_attendance';

    protected $fillable = [
        'event_id',
        'user_id',
        'attended',
        'reported_by_user_id',
        'reported_at',
        'bonus_awarded',
    ];

    protected $casts = [
        'attended' => 'boolean',
        'bonus_awarded' => 'boolean',
        'reported_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(GroupEvent::class, 'event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }
}
