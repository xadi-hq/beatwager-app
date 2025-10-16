# Phase 2 Implementation Plan: Point Decay & Events

**Created:** October 15, 2025
**Status:** Planning Phase
**Priority Features:** Point Decay System, Events System

---

## Overview

This document outlines the implementation plan for two Phase 2 features:
1. **Point Decay System** - Encourage engagement through 5% weekly decay after 14 days inactivity
2. **Events System** - Track attendance at real-world events with point bonuses (excluding challenge functionality for now)

---

## Feature 1: Point Decay System

### Business Rules (from FIRST_CHAT.md:505)

1. **Grace period:** First 14 days after joining group (no decay)
2. **Activity tracking:** Joining any wager resets decay timer
3. **Decay trigger:** If no wagers joined in 14+ days
4. **Decay rate:** 5% of current balance per week (min 50pts, max 100pts)
5. **Warning notification:** Sent on day 12 of inactivity
6. **Scheduled execution:** Daily at 1:00 AM

### Implementation Steps

#### 1. Database Changes

**Migration: `add_last_wager_joined_at_to_user_group`**
```php
Schema::table('user_group', function (Blueprint $table) {
    $table->timestamp('last_wager_joined_at')->nullable()->after('current_points');
    $table->timestamp('last_decay_applied_at')->nullable()->after('last_wager_joined_at');
    $table->timestamp('decay_warning_sent_at')->nullable()->after('last_decay_applied_at');
});
```

**Update Transaction types enum:**
```php
// In transactions table migration or enum
'decay_penalty' // Add to transaction type enum
```

#### 2. Service Layer Updates

**File: `app/Services/PointService.php`**

Add methods:
```php
/**
 * Apply point decay for inactive users in a group
 * Called by scheduled job daily
 */
public function applyDecayForGroup(Group $group): array

/**
 * Calculate decay amount for a user
 * 5% of balance, min 50pts, max 100pts
 */
public function calculateDecayAmount(int $currentPoints): int

/**
 * Check if user should receive decay warning (day 12 of inactivity)
 */
public function shouldSendDecayWarning(UserGroup $userGroup): bool

/**
 * Send decay warning notification
 */
public function sendDecayWarning(User $user, Group $group): void
```

**File: `app/Services/WagerService.php`**

Update `joinWager()` method:
```php
// After successful join, update last_wager_joined_at
$userGroup = UserGroup::where('user_id', $user->id)
    ->where('group_id', $wager->group_id)
    ->first();

$userGroup->update(['last_wager_joined_at' => now()]);
```

#### 3. Scheduled Job

**File: `app/Jobs/ApplyPointDecay.php`**
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PointService;
use App\Models\Group;

class ApplyPointDecay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PointService $pointService): void
    {
        // Process all groups
        Group::chunk(50, function ($groups) use ($pointService) {
            foreach ($groups as $group) {
                $pointService->applyDecayForGroup($group);
            }
        });
    }
}
```

**Register in `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule)
{
    // Existing schedules...

    $schedule->job(new ApplyPointDecay)
        ->dailyAt('01:00')
        ->withoutOverlapping()
        ->onOneServer();
}
```

#### 4. Notifications

**Update: `app/Services/Messaging/TelegramMessenger.php`**

Add methods:
```php
public function sendDecayWarning(User $user, Group $group, int $daysInactive): void
{
    $message = "⚠️ Inactivity Warning!\n\n";
    $message .= "You haven't joined a wager in {$daysInactive} days.\n";
    $message .= "In 2 days, you'll lose 5% of your points (min 50pts).\n\n";
    $message .= "Join a wager to reset the timer!";

    $this->sendPrivateMessage($user->telegram_id, $message);
}

public function sendDecayApplied(User $user, Group $group, int $amount, int $newBalance): void
{
    $message = "📉 Point Decay Applied\n\n";
    $message .= "You lost {$amount} points due to inactivity.\n";
    $message .= "New balance: {$newBalance} points\n\n";
    $message .= "Join a wager to stop decay!";

    $this->sendPrivateMessage($user->telegram_id, $message);
}
```

#### 5. Testing

**File: `tests/Feature/PointDecayTest.php`**

Test cases:
- User joins wager → `last_wager_joined_at` updated
- User inactive for 12 days → warning sent
- User inactive for 15 days → decay applied (5%, min 50, max 100)
- User with 1000 points → loses 50 points (capped at min)
- User with 5000 points → loses 100 points (capped at max)
- User with 1500 points → loses 75 points (5% = 75)
- User joins wager after 20 days → no decay on day 21
- Grace period: New user → no decay for first 14 days

---

## Feature 2: Events System

### Business Rules (from FIRST_CHAT.md:896)

**Simplified Flow (No Challenge System for V1):**
1. Event creation via `/newevent` → web form
2. Optional RSVP via Telegram buttons
3. Post-event attendance recording (2 hours after event)
4. Anyone can submit attendance via web link
5. Point bonuses distributed immediately
6. **Skip challenge system for V1** (can add later)

### Implementation Steps

#### 1. Database Changes

**Migration: `create_group_events_table`**
```php
Schema::create('group_events', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('group_id');
    $table->uuid('created_by_user_id');
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamp('event_date');
    $table->string('location')->nullable();
    $table->integer('attendance_bonus')->default(100);
    $table->timestamp('rsvp_deadline')->nullable();
    $table->integer('auto_prompt_hours_after')->default(2);
    $table->enum('status', ['upcoming', 'completed', 'expired', 'cancelled'])->default('upcoming');
    $table->timestamps();

    $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
    $table->foreign('created_by_user_id')->references('id')->on('users');

    $table->index(['group_id', 'status']);
    $table->index('event_date');
});
```

**Migration: `create_group_event_rsvps_table`**
```php
Schema::create('group_event_rsvps', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('event_id');
    $table->uuid('user_id');
    $table->enum('response', ['going', 'maybe', 'not_going']);
    $table->timestamps();

    $table->foreign('event_id')->references('id')->on('group_events')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users');

    $table->unique(['event_id', 'user_id']);
});
```

**Migration: `create_group_event_attendance_table`**
```php
Schema::create('group_event_attendance', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('event_id');
    $table->uuid('user_id');
    $table->boolean('attended');
    $table->uuid('reported_by_user_id');
    $table->timestamp('reported_at');
    $table->boolean('bonus_awarded')->default(false);
    $table->timestamps();

    $table->foreign('event_id')->references('id')->on('group_events')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users');
    $table->foreign('reported_by_user_id')->references('id')->on('users');

    $table->unique(['event_id', 'user_id']);
    $table->index(['event_id', 'attended']);
});
```

**Update transaction types:**
```php
'event_attendance_bonus' // Add to transaction type enum
```

#### 2. Models

**File: `app/Models/GroupEvent.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupEvent extends Model
{
    use HasUuids;

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

    public function needsAttendancePrompt(): bool
    {
        if ($this->status !== 'upcoming') {
            return false;
        }

        $promptTime = $this->event_date->addHours($this->auto_prompt_hours_after);
        return now()->greaterThanOrEqualTo($promptTime) && $this->attendance()->count() === 0;
    }
}
```

**Similar models for `GroupEventRsvp` and `GroupEventAttendance`**

#### 3. Service Layer

**File: `app/Services/EventService.php`**
```php
<?php

namespace App\Services;

use App\Models\GroupEvent;
use App\Models\Group;
use App\Models\User;
use App\Models\GroupEventRsvp;
use App\Models\GroupEventAttendance;
use Illuminate\Support\Collection;

class EventService
{
    public function __construct(
        private PointService $pointService,
        private MessagingServiceInterface $messagingService
    ) {}

    /**
     * Create a new group event
     */
    public function createEvent(Group $group, User $creator, array $data): GroupEvent
    {
        $event = GroupEvent::create([
            'group_id' => $group->id,
            'created_by_user_id' => $creator->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'event_date' => $data['event_date'],
            'location' => $data['location'] ?? null,
            'attendance_bonus' => $data['attendance_bonus'] ?? 100,
            'rsvp_deadline' => $data['rsvp_deadline'] ?? null,
            'auto_prompt_hours_after' => $data['auto_prompt_hours_after'] ?? 2,
        ]);

        // Announce to group
        $this->messagingService->announceNewEvent($event);

        return $event;
    }

    /**
     * Record RSVP for an event
     */
    public function recordRsvp(GroupEvent $event, User $user, string $response): GroupEventRsvp
    {
        return GroupEventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            ['response' => $response]
        );
    }

    /**
     * Record attendance for an event
     * Anyone can submit, first submission wins
     */
    public function recordAttendance(GroupEvent $event, User $reporter, array $attendeeIds): void
    {
        // Check if already recorded
        if ($event->attendance()->exists()) {
            throw new \Exception('Attendance already recorded for this event');
        }

        $groupUsers = $event->group->users()->get();

        foreach ($groupUsers as $user) {
            $attended = in_array($user->id, $attendeeIds);

            GroupEventAttendance::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'attended' => $attended,
                'reported_by_user_id' => $reporter->id,
                'reported_at' => now(),
            ]);

            // Award bonus to attendees
            if ($attended) {
                $this->pointService->grantEventBonus($user, $event);
            }
        }

        // Update event status
        $event->update(['status' => 'completed']);

        // Announce results
        $this->messagingService->announceAttendanceRecorded($event, $reporter);
    }

    /**
     * Get events for display
     */
    public function getEventsForGroup(Group $group): array
    {
        return [
            'upcoming' => $group->events()->where('status', 'upcoming')->orderBy('event_date')->get(),
            'past_processed' => $group->events()->where('status', 'completed')->orderByDesc('event_date')->get(),
            'past_unprocessed' => $group->events()
                ->where('status', 'upcoming')
                ->where('event_date', '<', now())
                ->orderByDesc('event_date')
                ->get(),
        ];
    }
}
```

**Update: `app/Services/PointService.php`**
```php
/**
 * Grant event attendance bonus
 */
public function grantEventBonus(User $user, GroupEvent $event): void
{
    $userGroup = UserGroup::where('user_id', $user->id)
        ->where('group_id', $event->group_id)
        ->lockForUpdate()
        ->first();

    $amount = $event->attendance_bonus;
    $newBalance = $userGroup->current_points + $amount;

    // Update balance
    $userGroup->update(['current_points' => $newBalance]);

    // Create transaction
    Transaction::create([
        'user_id' => $user->id,
        'group_id' => $event->group_id,
        'amount' => $amount,
        'type' => 'event_attendance_bonus',
        'reference_id' => $event->id,
        'balance_after' => $newBalance,
    ]);

    // Update last activity (attending event counts as activity for decay)
    $userGroup->update(['last_wager_joined_at' => now()]);
}
```

#### 4. Bot Command Handler

**Update: `app/Http/Controllers/TelegramWebhookController.php`**
```php
private function handleNewEventCommand(array $message): void
{
    $userId = $message['from']['id'];
    $chatId = $message['chat']['id'];

    // Generate one-time token
    $token = $this->tokenService->generateCreationToken(
        userId: $userId,
        groupId: $chatId,
        action: 'create_event',
        platform: 'telegram'
    );

    $url = route('events.create', ['token' => $token]);
    $shortUrl = $this->shortUrlService->create($url);

    // Send private message with link
    $this->telegramMessenger->sendPrivateMessage(
        $userId,
        "📅 Create a new event:\n{$shortUrl}"
    );
}
```

#### 5. Web Routes

**File: `routes/web.php`**
```php
// Events
Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::post('/events/create', [EventController::class, 'store'])->name('events.store');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/attendance', [EventController::class, 'attendance'])->name('events.attendance');
Route::post('/events/{event}/attendance', [EventController::class, 'recordAttendance'])->name('events.recordAttendance');
Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
```

#### 6. Controller

**File: `app/Http/Controllers/EventController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Models\GroupEvent;
use App\Services\EventService;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function __construct(
        private EventService $eventService,
        private TokenService $tokenService
    ) {}

    public function create(Request $request)
    {
        // Validate token and get context
        $token = $this->tokenService->validateToken($request->query('token'));
        $group = Group::findOrFail($token->context['group_id']);

        return Inertia::render('Events/Create', [
            'group' => $group,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
            'attendance_bonus' => 'required|integer|min:0|max:1000',
            'rsvp_deadline' => 'nullable|date|before:event_date',
            'auto_prompt_hours_after' => 'required|integer|min:0|max:48',
        ]);

        $event = $this->eventService->createEvent(
            auth()->user()->groups()->first(), // Simplified
            auth()->user(),
            $validated
        );

        return redirect()->route('me')->with('success', 'Event created!');
    }

    public function attendance(Request $request, GroupEvent $event)
    {
        // Show attendance recording form
        $groupUsers = $event->group->users()->get();

        return Inertia::render('Events/Attendance', [
            'event' => $event->load('group', 'rsvps'),
            'users' => $groupUsers,
        ]);
    }

    public function recordAttendance(Request $request, GroupEvent $event)
    {
        $validated = $request->validate([
            'attendee_ids' => 'required|array',
            'attendee_ids.*' => 'exists:users,id',
        ]);

        $this->eventService->recordAttendance(
            $event,
            auth()->user(),
            $validated['attendee_ids']
        );

        return redirect()->route('me')->with('success', 'Attendance recorded!');
    }

    public function rsvp(Request $request, GroupEvent $event)
    {
        $validated = $request->validate([
            'response' => 'required|in:going,maybe,not_going',
        ]);

        $this->eventService->recordRsvp(
            $event,
            auth()->user(),
            $validated['response']
        );

        return back();
    }
}
```

#### 7. Vue Components

**File: `resources/js/Pages/Events/Create.vue`**
- Copy structure from `Wagers/Create.vue`
- Adapt fields for events (name, date, location, bonus)
- Date/time picker for event_date
- Number input for attendance_bonus
- Optional fields: description, location, RSVP deadline

**File: `resources/js/Pages/Events/Attendance.vue`**
- Show event details
- Checklist of all group members
- Submit button
- Warning about first submission winning

**Update: `resources/js/Pages/Me.vue`**
- Add new tab: "Events"
- Show three sections:
  - Upcoming events
  - Past events (processed)
  - Past events (unprocessed) - highlighted
- Display: name, date, location, bonus, RSVP status

#### 8. Messaging Integration

**Update: `app/Services/Messaging/TelegramMessenger.php`**
```php
public function announceNewEvent(GroupEvent $event): void
{
    $message = "🎉 New Event: {$event->name}\n\n";
    $message .= "📅 When: " . $event->event_date->format('M d, Y g:i A') . "\n";

    if ($event->location) {
        $message .= "📍 Where: {$event->location}\n";
    }

    $message .= "💰 Bonus: +{$event->attendance_bonus} points for attending!\n";

    if ($event->rsvp_deadline) {
        $message .= "🎟️ RSVP by: " . $event->rsvp_deadline->format('M d') . "\n";
    }

    $buttons = [
        [
            ['text' => 'Going 👍', 'callback_data' => "event_rsvp:{$event->id}:going"],
            ['text' => 'Maybe 🤔', 'callback_data' => "event_rsvp:{$event->id}:maybe"],
            ['text' => 'Can\'t Make It 👎', 'callback_data' => "event_rsvp:{$event->id}:not_going"],
        ]
    ];

    $this->sendMessage($event->group->telegram_chat_id, $message, $buttons);
}

public function sendAttendancePrompt(GroupEvent $event): void
{
    $url = route('events.attendance', ['event' => $event->id]);
    $shortUrl = $this->shortUrlService->create($url);

    $message = "📋 Who attended {$event->name}?\n\n";
    $message .= "Record attendance here:\n{$shortUrl}\n\n";
    $message .= "⚠️ Anyone can submit, first submission wins!";

    $this->sendMessage($event->group->telegram_chat_id, $message);
}

public function announceAttendanceRecorded(GroupEvent $event, User $reporter): void
{
    $attendees = $event->attendance()->where('attended', true)->with('user')->get();

    $message = "✅ Attendance recorded for {$event->name}\n\n";
    $message .= "Attended (" . $attendees->count() . "):\n";

    foreach ($attendees as $attendance) {
        $message .= "• @{$attendance->user->username}\n";
    }

    $message .= "\nEach received +{$event->attendance_bonus} points!\n\n";
    $message .= "Recorded by: @{$reporter->username}";

    $this->sendMessage($event->group->telegram_chat_id, $message);
}
```

#### 9. Scheduled Job for Attendance Prompts

**File: `app/Jobs/SendEventAttendancePrompts.php`**
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GroupEvent;
use App\Services\Messaging\MessagingServiceInterface;

class SendEventAttendancePrompts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(MessagingServiceInterface $messagingService): void
    {
        // Find events that need attendance prompts
        $events = GroupEvent::where('status', 'upcoming')
            ->whereNotNull('event_date')
            ->get()
            ->filter(function ($event) {
                return $event->needsAttendancePrompt();
            });

        foreach ($events as $event) {
            $messagingService->sendAttendancePrompt($event);
        }
    }
}
```

**Register in `app/Console/Kernel.php`:**
```php
$schedule->job(new SendEventAttendancePrompts)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
```

#### 10. Testing

**File: `tests/Feature/EventsTest.php`**

Test cases:
- Event creation flow with token
- RSVP updates
- Attendance recording (first submission wins)
- Point bonus distribution
- Multiple users trying to submit (second rejected)
- Event status transitions
- Attendance prompts sent at correct time
- Past unprocessed events displayed correctly

---

## Implementation Order

### Phase 1: Point Decay (Estimated: 1-2 days)
1. Database migration (10 min)
2. PointService decay methods (2-3 hours)
3. Update WagerService join tracking (30 min)
4. Create ApplyPointDecay job (1 hour)
5. Schedule job registration (10 min)
6. Telegram notifications (1 hour)
7. Testing (2-3 hours)

### Phase 2: Events System (Estimated: 3-4 days)
1. Database migrations (30 min)
2. Models (1 hour)
3. EventService (3-4 hours)
4. PointService event bonus method (30 min)
5. Bot command handler (1 hour)
6. EventController (2 hours)
7. Event creation form Vue component (3-4 hours)
8. Attendance recording form (2-3 hours)
9. Events tab on dashboard (2 hours)
10. Telegram messaging integration (2-3 hours)
11. Scheduled attendance prompts (1 hour)
12. Testing (3-4 hours)

**Total Estimated Time: 4-6 days**

---

## Notes

- **Challenge system** is explicitly excluded from V1 (can add in Phase 2.1)
- Events count as "activity" for decay purposes (resets decay timer)
- RSVP is purely informational (no penalties)
- First attendance submission wins (no duplicates)
- Auto-prompt timing is configurable per event (default 2 hours)

---

## Success Criteria

**Point Decay:**
- ✅ Users tracked for last wager join
- ✅ Warning sent on day 12
- ✅ Decay applied correctly on day 15+
- ✅ Joining wager resets timer
- ✅ No decay during grace period

**Events:**
- ✅ Event creation via `/newevent` works
- ✅ Events announced to group
- ✅ RSVP buttons functional
- ✅ Attendance recording works
- ✅ Points distributed correctly
- ✅ Events tab shows correct categorization
- ✅ Auto-prompts sent at correct time
