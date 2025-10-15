# Event Streak LLM Integration

## Overview
Implemented streak context enrichment for LLM-generated event announcements and attendance messages. The system now automatically includes streak information to create engaging, personalized messages that highlight achievements and encourage participation.

## Implementation Date
October 28, 2025

## Changes Made

### 1. MessageService Enhancements

#### `eventAnnouncement()` Method ([MessageService.php:500-598](app/Services/MessageService.php#L500-L598))
**Purpose**: Add streak context to event announcements showing users whose streaks are at risk

**Changes**:
- Queries `event_streak_configs` table to check if streaks are enabled
- Fetches top 5 users with active attendance streaks from `group_user` pivot table
- Calculates current multiplier for each streak using `EventStreakConfig::getMultiplierForStreak()`
- Passes streak data to LLM via MessageContext data array

**Data Provided to LLM**:
```php
[
    'streaks_at_risk' => [
        ['name' => 'John', 'streak' => 8, 'multiplier' => 1.2],
        ['name' => 'Sarah', 'streak' => 12, 'multiplier' => 1.5],
        // ... up to 5 users
    ],
    'has_active_streaks' => true,
    'streak_instructions' => 'Conversational prompt for LLM...'
]
```

**LLM Behavior**:
- Naturally mentions that missing the event will break streaks
- Uses conversational language: "Will Sarah keep her 12-event streak alive?"
- Only activates when `has_active_streaks` is true

---

#### `attendanceRecorded()` Method ([MessageService.php:637-684](app/Services/MessageService.php#L637-L684))
**Purpose**: Celebrate attendance and highlight streak achievements

**Changes**:
- Enhanced attendee data collection to include `streak_at_time` and `multiplier_applied`
- Calls new `buildStreakContext()` helper to generate comprehensive streak data
- Fetches next upcoming event for "keep it going" messaging
- Passes enriched context to LLM

**Data Provided to LLM**:
```php
[
    'streak_context' => [
        'enabled' => true,
        'top_streaks' => [
            ['name' => 'John', 'streak' => 8, 'multiplier' => 1.2],
            // ... top 3 streaks
        ],
        'milestones' => [
            ['name' => 'Sarah', 'streak' => 10, 'multiplier' => 1.5],
            // ... anyone who hit 5, 10, 15, 20, 25, 30, 50, or 100
        ],
        'next_event' => [
            'name' => 'Movie Night',
            'date' => 'Nov 5, 2025',
            'days_until' => 7
        ],
        'llm_instructions' => '...'
    ]
]
```

**LLM Behavior**:
- Highlights top 3 streaks naturally: "John's on an 8-event streak!"
- Celebrates milestones enthusiastically: "Sarah just hit 10 events in a row!"
- Subtly encourages continuation: "Will they keep it going at Movie Night next week?"
- Avoids formulaic or preachy language

---

#### `buildStreakContext()` Helper Method ([MessageService.php:377-444](app/Services/MessageService.php#L377-L444))
**Purpose**: Centralized streak context builder for attendance announcements

**Logic**:
1. **Disables if not configured**: Returns empty structure if `EventStreakConfig` is not enabled
2. **Top Streaks**: Sorts attendees by streak count, takes top 3 with active streaks
3. **Milestone Detection**: Identifies users who hit specific milestones (5, 10, 15, 20, 25, 30, 50, 100)
4. **Next Event Lookup**: Queries for next upcoming event in same group
5. **LLM Instructions**: Provides clear guidance for natural, conversational messaging

**Return Structure**:
```php
[
    'enabled' => bool,
    'top_streaks' => array,      // Top 3 active streaks
    'milestones' => array,        // Milestone achievements this event
    'next_event' => array|null,   // Next event info or null
    'llm_instructions' => string  // Clear guidance for LLM
]
```

---

### 2. Message Template Updates

#### Event Announced Template ([lang/en/messages.php:46-58](lang/en/messages.php#L46-L58))
**Changes**:
- Added `optional_fields`: `['streaks_at_risk', 'has_active_streaks', 'streak_instructions']`
- Added `personality_notes` with streak mention guidance
- Increased `max_words` from 40 to 50 to accommodate streak mentions
- Added examples showing with/without streak scenarios

**Example Messages**:
- **With streaks**: "🎉 Game Night this Friday! Will Sarah keep her 12-event streak going? 👀"
- **Without streaks**: "🎉 Game Night this Friday! RSVP now for +100 points!"

---

#### Attendance Recorded Template ([lang/en/messages.php:59-71](lang/en/messages.php#L59-L71))
**Changes**:
- Added `optional_fields`: `['streak_context', 'has_streaks']`
- Enhanced `intent` to include "with streak achievements"
- Updated `tone_hints` to include "enthusiastic"
- Added detailed `personality_notes` for streak highlighting
- Increased `max_words` from 50 to 60 for streak celebrations
- Added examples showing comprehensive streak messaging

**Example Messages**:
- **With streaks**: "🔥 Game Night complete! John's on an 8-event streak now! Sarah hit the 10-event milestone! Will they keep it going at Movie Night next week?"
- **Without streaks**: "✅ Game Night was a blast! 5 people showed up and earned +100 points each!"

---

## Technical Details

### Database Schema Used
- `event_streak_configs`: Configuration table for streak settings
  - `group_id`: Which group has streaks enabled
  - `enabled`: Boolean flag
  - `multiplier_tiers`: JSON array of tier definitions

- `group_user` pivot table: Tracks individual user streaks
  - `event_attendance_streak`: Current streak count
  - `last_event_attended_at`: Last attendance date for streak validation

- `group_event_attendances`: Attendance records with streak snapshot
  - `streak_at_time`: Streak count when attending
  - `multiplier_applied`: Multiplier used for point calculation

### Integration Points
1. **Event Creation Flow**: `SendEventAnnouncement` listener → `MessageService::eventAnnouncement()`
2. **Attendance Recording Flow**: `SendAttendanceAnnouncement` listener → `MessageService::attendanceRecorded()`
3. **LLM Generation**: `ContentGenerator::generate()` receives enriched context via `MessageContext`

### Performance Considerations
- **Event Announcement**: Single query for active streaks (top 5), indexed on `event_attendance_streak`
- **Attendance Recording**: Single query for next event, minimal processing for milestone detection
- **Caching**: Not implemented yet - consider caching EventStreakConfig per group

---

## Testing

### Automated Tests
- ✅ PHP syntax validation passed
- ✅ Event-related tests passed (51/52 - 1 unrelated failure)
- ✅ Manual tinker test confirmed message generation works

### Test Command
```bash
docker exec beatwager-app php artisan test --filter=Event
```

### Manual Verification
```bash
docker exec beatwager-app php artisan tinker --execute="
\$group = App\Models\Group::first();
\$event = App\Models\GroupEvent::factory()->create(['group_id' => \$group->id]);
\$messageService = app(App\Services\MessageService::class);
\$message = \$messageService->eventAnnouncement(\$event);
echo \$message->content;
"
```

---

## Usage Examples

### Example 1: Event Announcement with Active Streaks
**Input**:
- Event: "Game Night"
- Active streaks: John (8 events), Sarah (12 events), Mike (5 events)

**LLM Receives**:
```php
[
    'name' => 'Game Night',
    'event_date' => 'Nov 1, 2025 7:00 PM',
    'attendance_bonus' => 100,
    'streaks_at_risk' => [
        ['name' => 'John', 'streak' => 8, 'multiplier' => 1.2],
        ['name' => 'Sarah', 'streak' => 12, 'multiplier' => 1.5],
        ['name' => 'Mike', 'streak' => 5, 'multiplier' => 1.1],
    ],
    'has_active_streaks' => true,
]
```

**Possible Output**:
> 🎉 Game Night is this Friday at 7 PM!
>
> Will Sarah keep her 12-event streak alive? John's at 8 in a row! 👀
>
> +100 points for attending! RSVP now!

---

### Example 2: Attendance Recording with Milestones
**Input**:
- Event: "Game Night" (completed)
- Attendees: John (now at 8 events), Sarah (now at 10 events - milestone!), Mike (now at 6 events)
- Next event: "Movie Night" in 7 days

**LLM Receives**:
```php
[
    'streak_context' => [
        'enabled' => true,
        'top_streaks' => [
            ['name' => 'Sarah', 'streak' => 10, 'multiplier' => 1.5],
            ['name' => 'John', 'streak' => 8, 'multiplier' => 1.2],
            ['name' => 'Mike', 'streak' => 6, 'multiplier' => 1.15],
        ],
        'milestones' => [
            ['name' => 'Sarah', 'streak' => 10, 'multiplier' => 1.5],
        ],
        'next_event' => [
            'name' => 'Movie Night',
            'date' => 'Nov 8, 2025',
            'days_until' => 7,
        ],
    ],
]
```

**Possible Output**:
> 🔥 Game Night was epic!
>
> 🏆 Sarah just hit the 10-event milestone! John's at 8 in a row! Mike's keeping the streak going at 6!
>
> Will they keep it alive at Movie Night next week? 👀

---

## Next Steps (Remaining Tasks)

Based on the original plan, here are the remaining streak implementation tasks:

### Task 8: Profile & Announcement Display
- [ ] Add streak badges to user profile pages
- [ ] Display current streak and multiplier in profile UI
- [ ] Show streak history and milestone achievements
- [ ] Add streak visualizations (graphs, progress bars)

### Future Enhancements
- [ ] Add streak leaderboard to group dashboard
- [ ] Implement streak milestone badges (5th, 10th, 20th attendance)
- [ ] Add push notifications for streak milestones
- [ ] Create streak protection feature (one grace skip)
- [ ] Add seasonal streak competitions

---

## Files Modified

1. **app/Services/MessageService.php**
   - Enhanced `eventAnnouncement()` method (lines 500-598)
   - Enhanced `attendanceRecorded()` method (lines 637-684)
   - Added `buildStreakContext()` helper (lines 377-444)

2. **lang/en/messages.php**
   - Updated `event.announced` template (lines 46-58)
   - Updated `event.attendance_recorded` template (lines 59-71)

---

## Design Decisions

### Why Pass Streak Context Instead of Current Values?
We pass **historical streak data** (streak_at_time, multiplier_applied from attendance records) rather than querying current values because:
1. **Accuracy**: Reflects the actual streak when attendance was recorded
2. **Audit Trail**: Maintains historical integrity for point calculation verification
3. **Immutability**: Past announcements remain accurate even if streaks change

### Why Top 3 for Attendance vs Top 5 for Announcements?
- **Announcements**: Top 5 to cast a wider net for engagement ("at risk" messaging)
- **Attendance**: Top 3 to keep celebrations focused and avoid message bloat

### Why Milestone Thresholds at 5, 10, 15, 20, etc.?
Standard milestone intervals balance:
- **Frequent enough**: Users see progress regularly
- **Meaningful**: Not every event is celebrated, preserving impact
- **Scalable**: Works for both new and long-term members

### Why Include Next Event Info?
Creates narrative continuity:
- Encourages streak maintenance
- Drives RSVP to next event
- Creates anticipation and FOMO

---

## Conclusion

The streak LLM integration is now complete and functional. The system will automatically:
1. ✅ Detect active streaks when announcing events
2. ✅ Mention streak risks conversationally to drive engagement
3. ✅ Celebrate streak achievements when recording attendance
4. ✅ Highlight milestones enthusiastically
5. ✅ Encourage continuation by mentioning next events

The LLM will generate natural, engaging messages that make streaks feel like a fun, social achievement rather than a mechanical system.
