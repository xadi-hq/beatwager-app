# RSVP Message Personality Guide

**Status**: ✅ Complete
**Last Updated**: October 16, 2025

## Overview

Event RSVP messages use LLM to create emotional, playful responses that match the sentiment of each RSVP type.

## Message Types

### 1. RSVP Going - Party Time! 🎉

**Intent**: Celebrate someone joining the event - party time!
**Tone**: Excited, celebratory, party
**Max Words**: 20

**Configuration** ([messages.php:55-60](file:///home/xander/webapps/beatwager-app/lang/en/messages.php#L55-L60)):
```php
'rsvp_going' => [
    'intent' => 'Celebrate someone joining the event - party time!',
    'required_fields' => ['user_name', 'event_name'],
    'fallback_template' => "🎉 {user_name} is coming to {event_name}!",
    'tone_hints' => ['excited', 'celebratory', 'party'],
    'max_words' => 20,
],
```

**Example LLM Outputs**:
```
✅ BEFORE (hardcoded): "@xander RSVP'd 'Going' for: Monthly dinner"

🎉 AFTER (LLM with personality):
- "🎉 YES! Xander is joining Monthly dinner! Let's gooo! 🔥"
- "🥳 Xander's in for Monthly dinner! Who else is coming?! 🙌"
- "🎊 Party time! Xander just confirmed for Monthly dinner! 💃"
- "✨ Xander is coming to Monthly dinner! The squad is growing! 🚀"
```

---

### 2. RSVP Maybe - Playful Teasing 🤔

**Intent**: Playfully tease someone who is undecided
**Tone**: Playful, teasing, lighthearted
**Max Words**: 20

**Configuration** ([messages.php:62-68](file:///home/xander/webapps/beatwager-app/lang/en/messages.php#L62-L68)):
```php
'rsvp_maybe' => [
    'intent' => 'Playfully tease someone who is undecided',
    'required_fields' => ['user_name', 'event_name'],
    'fallback_template' => "🤔 {user_name} might come to {event_name}... or might not 🤷",
    'tone_hints' => ['playful', 'teasing', 'lighthearted'],
    'max_words' => 20,
],
```

**Example LLM Outputs**:
```
🤔 BEFORE (hardcoded): "@xander RSVP'd 'Maybe' for: Monthly dinner"

🤷 AFTER (LLM with personality):
- "🤔 Xander's playing hard to get with Monthly dinner... make up your mind! 😏"
- "🎭 Classic Xander - maybe yes, maybe no for Monthly dinner! Commitment issues? 😂"
- "🤷 Xander's on the fence for Monthly dinner. We'll see if they show! 👀"
- "😅 Monthly dinner has a 'maybe' from Xander. Better than nothing? 🤞"
```

---

### 3. RSVP Not Going - Dramatic Guilt-Trip 😢

**Intent**: Express disappointment that someone cannot make it
**Tone**: Disappointed, dramatic, guilt-trip
**Max Words**: 20

**Configuration** ([messages.php:70-76](file:///home/xander/webapps/beatwager-app/lang/en/messages.php#L70-L76)):
```php
'rsvp_not_going' => [
    'intent' => 'Express disappointment that someone cannot make it',
    'required_fields' => ['user_name', 'event_name'],
    'fallback_template' => "😢 {user_name} can't make it to {event_name}",
    'tone_hints' => ['disappointed', 'dramatic', 'guilt-trip'],
    'max_words' => 20,
],
```

**Example LLM Outputs**:
```
❌ BEFORE (hardcoded): "@xander RSVP'd 'Can't Make It' for: Monthly dinner"

😢 AFTER (LLM with personality):
- "💔 Nooo! Xander can't make Monthly dinner... They'd better be the only one! 😭"
- "😭 Xander bailing on Monthly dinner?! Say it ain't so! We'll miss you! 🥺"
- "🚨 ABANDON SHIP! Xander just ditched Monthly dinner. Hope you have a good excuse! 😤"
- "😢 Darn it! Xander's out for Monthly dinner. Fine... more food for us! 🍕"
- "💀 Xander flaked on Monthly dinner. We won't forget this betrayal! 😂"
```

---

## Technical Implementation

### Event Flow

```
User clicks RSVP button in Telegram
  ↓
TelegramWebhookController::handleEventRsvpCallback()
  ↓
EventRsvp record created/updated
  ↓
EventRsvpUpdated::dispatch($event, $user, $response)
  ↓
SendRsvpAnnouncement (queued listener)
  ↓
MessageService::rsvpUpdated($event, $user, $response)
  ↓
LLMService::generate($context) with tone hints
  ↓
Group::sendMessage() → Telegram announcement
```

### Key Files

- **Event**: [/app/Events/EventRsvpUpdated.php](file:///home/xander/webapps/beatwager-app/app/Events/EventRsvpUpdated.php)
- **Listener**: [/app/Listeners/SendRsvpAnnouncement.php](file:///home/xander/webapps/beatwager-app/app/Listeners/SendRsvpAnnouncement.php)
- **Message Config**: [/lang/en/messages.php:55-77](file:///home/xander/webapps/beatwager-app/lang/en/messages.php#L55-L77)
- **MessageService**: [/app/Services/MessageService.php:378-412](file:///home/xander/webapps/beatwager-app/app/Services/MessageService.php#L378-L412)
- **Controller**: [/app/Http/Controllers/Api/TelegramWebhookController.php:990](file:///home/xander/webapps/beatwager-app/app/Http/Controllers/Api/TelegramWebhookController.php#L990)

### MessageService Implementation

```php
public function rsvpUpdated(GroupEvent $event, User $user, string $response): Message
{
    // Map response to message key
    $messageKey = match ($response) {
        'going' => 'event.rsvp_going',
        'maybe' => 'event.rsvp_maybe',
        'not_going' => 'event.rsvp_not_going',
    };

    $meta = __($messageKey);

    $ctx = new MessageContext(
        key: $messageKey,
        intent: $meta['intent'],
        requiredFields: $meta['required_fields'],
        data: [
            'user_name' => $user->name,
            'event_name' => $event->name,
            'event_date' => $event->event_date->format('M j, Y'),
        ],
        group: $event->group
    );

    $content = $this->contentGenerator->generate($ctx, $event->group);
    return new Message(/*...*/);
}
```

---

## Tone Guidance for LLM

The LLM receives these tone hints in the prompt:

### Going (Celebratory)
```
Intent: Celebrate someone joining the event - party time!
Tone hints: excited, celebratory, party

Rules:
- Make it feel like a celebration
- Use exclamation marks and party emojis
- Create energy and excitement
- Encourage others to join
```

### Maybe (Teasing)
```
Intent: Playfully tease someone who is undecided
Tone hints: playful, teasing, lighthearted

Rules:
- Gentle teasing, never mean
- Lighthearted and funny
- Call out their indecision playfully
- Keep it friendly and fun
```

### Not Going (Guilt-Trip)
```
Intent: Express disappointment that someone cannot make it
Tone hints: disappointed, dramatic, guilt-trip

Rules:
- Dramatic but playful disappointment
- Over-the-top reactions (not serious)
- Funny guilt-tripping
- Make them feel missed (but keep it light)
```

---

## Testing

### Manual Test via Telegram
1. Create event in Telegram group
2. Click RSVP button (Going/Maybe/Can't Make It)
3. Check group for playful announcement
4. Try different responses to see personality variation

### Expected Behavior

**Going**:
- High energy, celebration
- Emojis: 🎉 🥳 🎊 ✨ 🔥 🙌 💃 🚀

**Maybe**:
- Playful teasing
- Emojis: 🤔 🤷 😏 😂 👀 🎭 😅 🤞

**Not Going**:
- Dramatic disappointment
- Emojis: 😢 😭 💔 🥺 😤 💀 🚨 ❌

---

## Customization

Groups can adjust personality via Bot Tone setting:

**Example Tone Settings**:

**Professional Group**:
```
Keep messages professional but friendly. Avoid over-the-top reactions.
```
Output: "✅ Xander confirmed for Monthly dinner. See you there!"

**Casual Friend Group**:
```
Be super casual, use slang, roast people playfully when they flake.
```
Output: "🔥 YO! Xander's pulling up to Monthly dinner! LET'S GOOOO! 🎉"

**Sports Team**:
```
Use sports metaphors and team spirit language.
```
Output: "🏆 Xander's on the roster for Monthly dinner! Team's looking strong! 💪"

---

## RSVP Change Detection ✅ IMPLEMENTED

When someone updates their RSVP, the system detects the change and uses special messages with extra personality!

### Changed to Going - Redemption! 🎉

**Tone**: Excited, redemption, celebratory | **Max Words**: 25

**Example Outputs**:
```
🎉 "REDEMPTION! Xander changed from 'not going' to GOING for Monthly dinner! Welcome back! 🙌"
✨ "Xander had a change of heart! Now coming to Monthly dinner after all! 💪"
🔥 "Look who's BACK! Xander flipped from 'maybe' to going for Monthly dinner! 🎊"
```

### Changed to Maybe - Getting Cold Feet 🤔

**Tone**: Playful, teasing, uncertain | **Max Words**: 25

**Example Outputs**:
```
🤔 "Uh oh... Xander downgraded from 'going' to maybe for Monthly dinner. Cold feet? 😅"
😏 "Xander's commitment is wavering! Changed to maybe for Monthly dinner. Classic! 🤷"
👀 "Plot twist! Xander went from 'going' to maybe for Monthly dinner. What happened?! 🎭"
```

### Changed to Not Going - Betrayal! 😭

**Tone**: Disappointed, dramatic, betrayal | **Max Words**: 25

**Example Outputs**:
```
😭 "BETRAYAL! Xander WAS going but changed to not going for Monthly dinner! 💔"
🚨 "Red alert! Xander FLIPPED from 'going' to NOT GOING! Absolute chaos! 😤"
💀 "Xander pulled the ultimate betrayal - ditched Monthly dinner after committing! 🥺"
```

---

## Future Enhancements

### Potential Additions:
- **First to RSVP**: "🏆 Xander is the FIRST to RSVP for Monthly dinner! Early bird! 🐛"
- **Last to RSVP**: "⏰ Fashionably late! Xander finally RSVP'd 5 minutes before event! 😅"
- **Group Momentum**: "📈 That's 8 people going! Monthly dinner is gonna be PACKED! 🔥"
- **Solo Attendee**: "😬 Xander is the only one going... awkward party of one? 🍽️"
- **Multiple Changes**: "🎢 Xander is on an RSVP rollercoaster! That's their 3rd change! 😂"

See also: [ENGAGEMENT_TRIGGERS.md](file:///home/xander/webapps/beatwager-app/docs/ENGAGEMENT_TRIGGERS.md) for wager engagement system.
