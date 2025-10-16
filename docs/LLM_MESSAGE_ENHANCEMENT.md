# LLM Message Enhancement System

## Overview

The LLM message enhancement system adds personality to bot messages while preserving information integrity and providing graceful fallbacks.

## Architecture

```
┌─────────────────┐
│  MessageService │  Creates structured Message DTOs from lang templates
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Group.send()   │  Coordinates message sending
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ LLMMessageEnhancer  │  Optionally enhances content with personality
└────────┬────────────┘
         │
         ▼  (if LLM configured)
┌─────────────────┐
│   LLMService    │  Calls LLM API with context
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ MessengerFactory│  Sends to platform (Telegram/Discord/etc)
└─────────────────┘
```

## Message Flow Example

### 1. **Original Message Creation**

```php
// In MessageService
public function wagerAnnouncement(Wager $wager): Message
{
    $template = __('messages.wager.announced'); // From lang file
    
    $variables = [
        'title' => $wager->title,
        'description' => $wager->description,
        'stake' => $wager->stake_amount,
        'deadline' => $wager->deadline->format('M j, Y'),
    ];
    
    return new Message(
        content: $template,  // "🎯 New Wager Created!\n\nQuestion: {title}..."
        type: MessageType::Announcement,
        variables: $variables,
        buttons: $this->buildWagerButtons($wager),
        context: $wager
    );
}
```

### 2. **Send Message (with optional enhancement)**

```php
// Usage
$message = $messageService->wagerAnnouncement($wager);
$wager->group->sendMessage($message);
```

### 3. **Enhancement Decision Logic**

```php
// In LLMMessageEnhancer::shouldEnhance()

// ✅ Enhanced if:
- Group has llm_api_key configured
- Group has bot_tone set  
- Message type is in ENHANCEABLE_MESSAGES list
- Group settings allow enhancement (not disabled)

// ❌ Not enhanced if:
- No LLM configured → use lang file
- Message is error/confirmation → keep simple
- Group disabled enhancement → respect preference
```

### 4. **Enhancement Process**

```php
// In LLMMessageEnhancer::enhance()

// Extract context from Message
$context = [
    'message_type' => 'announcement',
    'raw_content' => '🎯 New Wager Created...',
    'variables' => ['title' => 'Will John finish his marathon?', ...],
    'model_data' => ['creator' => 'Sarah', 'participants_count' => 0]
];

// Call LLM Service
$enhanced = $llmService->enhanceMessage($group, $context);

// Validate result
if (validateEnhanced($enhanced)) {
    return new Message(content: $enhanced, ...);  // Use enhanced version
}

return $message;  // Fallback to original
```

### 5. **LLM Prompt Structure**

```php
// System Prompt (from Group settings)
"You are BeatWager bot for a friends group.
Your tone: Sarcastic sports commentator who makes everything dramatic.
Keep responses 2-3 sentences max.
Include emojis."

// User Prompt (built from context)
"Rewrite this wager announcement with personality while keeping all facts:

Original: 🎯 New Wager Created!
Question: Will John finish his marathon?
Type: Yes/No
Stake: 50 points
Deadline: Oct 20, 2025

Required info to include:
- Question/title
- Wager type
- Stake amount
- Deadline
- Call-to-action to join

Rewrite with drama and personality:"
```

### 6. **Example Enhanced Output**

```
Before (lang file):
"🎯 New Wager Created!

Question: Will John finish his marathon?

Description: Boston Marathon 2025
Type: Yes/No
Stake: 50 points
Deadline: Oct 20, 2025

Click a button below to place your wager!"
```

```
After (LLM enhanced):
"🏃‍♂️ BREAKING: John's claiming he'll finish the Boston Marathon! 
Sarah's calling his bluff with 50 points on the line. 
Deadline: Oct 20. Pick your side - will he cross that finish line or bail at mile 20? 🎯"
```

## Configuration

### Group Settings

```php
$group->update([
    'llm_api_key' => 'sk-ant-...',  // Encrypted in database
    'llm_provider' => 'anthropic',
    'bot_tone' => 'Sarcastic sports commentator who makes everything dramatic',
    'group_type' => 'friends',
    'settings' => [
        'llm_enhancement_enabled' => true,  // Can disable per group
    ]
]);
```

### Enhanceable Message Types

Currently enhanced:
- `wager.announced` - When new wager is created
- `wager.settled` - When wager is resolved
- `wager.reminder` - Settlement reminders
- `reputation.decay` - Point decay announcements

Not enhanced (stay simple):
- `wager.joined` - Join confirmations
- Error messages
- Button labels
- System notifications

## Validation & Safety

### Content Validation

```php
// Enhanced messages must:
1. Contain key information from original (title, amounts, etc)
2. Be reasonable length (30-600 chars)
3. Maintain sentiment (keep emojis or similar)
4. Match context (wager vs event vs decay)

// If validation fails → use original message
```

### Cost Control

```php
// Built-in cost optimizations:
1. Cache similar prompts (1 hour TTL)
2. Only enhance high-value messages
3. Fallback to templates if API fails
4. Per-group toggle to disable
5. Use fast/cheap models (claude-haiku)
```

### Error Handling

```php
// Every step has graceful degradation:
try {
    $enhanced = $llm->enhance(...);
    if (validate($enhanced)) {
        return $enhanced;
    }
} catch (Exception $e) {
    Log::warning('LLM enhancement failed');
}
return $originalMessage;  // Always works
```

## Implementation Phases

### Phase 1: Foundation (Current)
- [x] Database migration for LLM fields
- [x] LLMMessageEnhancer service
- [ ] LLMService implementation
- [ ] Integration with Group.sendMessage()

### Phase 2: Enhancement
- [ ] Grudge memory context
- [ ] User history in prompts
- [ ] Badge mentions
- [ ] Leaderboard position references

### Phase 3: Advanced
- [ ] Multi-language support
- [ ] Custom personality presets
- [ ] A/B testing for tones
- [ ] Analytics on engagement

## Examples by Message Type

### Wager Announced

**Base Template:**
```
🎯 New Wager Created!
Question: {title}
Type: {type}
Stake: {stake} points
```

**With "Hype Man" Tone:**
```
🔥 YOOOO NEW WAGER ALERT! 
{creator} just dropped: "{title}"! 
{stake} points up for grabs! LET'S GO! 🚀
```

**With "Shakespearean" Tone:**
```
Hark! A new wager doth grace our gathering!
The question posed: "{title}"
{stake} points to the victor! ⚔️
```

### Wager Settled

**Base Template:**
```
🏁 Wager Settled!
Outcome: {outcome}
Winners: {winners}
```

**With "Roast Master" Tone:**
```
🎯 BOOM! {biggest_winner} just DESTROYED {biggest_loser}!
{outcome} was the answer and {loser} thought otherwise 😂
{winner} pockets {amount} points. Brutal. 💀
```

### Reputation Decay

**Base Template:**
```
📉 Point Decay
{user} lost {points} points due to inactivity.
```

**With "Sports Commentator" Tone:**
```
📉 AND THERE IT IS FOLKS! 
{user}'s empire is CRUMBLING! Down {points} points from lack of action!
Time to get back in the game! 🏈
```

## Best Practices

### DO:
✅ Keep original messages as templates
✅ Preserve all factual information  
✅ Fall back gracefully on errors
✅ Cache expensive LLM calls
✅ Validate output before using
✅ Log failures for debugging

### DON'T:
❌ Remove required information
❌ Change numbers/dates/facts
❌ Rely solely on LLM (always have fallback)
❌ Enhance every message (pick high-value)
❌ Allow unbounded response lengths
❌ Forget to encrypt API keys

## Testing

```php
// Test with mock LLM
$mockLLM = Mockery::mock(LLMService::class);
$mockLLM->shouldReceive('enhanceMessage')
    ->andReturn('Enhanced message');

$enhancer = new LLMMessageEnhancer($mockLLM);
$result = $enhancer->enhance($message, $group);

// Test fallback
$mockLLM->shouldReceive('enhanceMessage')
    ->andThrow(new Exception('API Error'));
    
$result = $enhancer->enhance($message, $group);
// Should return original message
```

## Monitoring

Key metrics to track:
- Enhancement success rate
- Validation failure rate
- API call latency
- Cost per message
- Fallback usage frequency
- User engagement (reactions/responses)

## Future Enhancements

1. **Context Memory**: Include recent group activity
2. **User Profiles**: Reference betting patterns
3. **Seasonal Events**: Special tones for holidays
4. **Dynamic Tones**: Adjust based on time of day
5. **Multi-modal**: Image generation for recaps
6. **Voice Messages**: TTS with personality

---

## Quick Start

1. **Enable for a group:**
```php
$group->update([
    'llm_api_key' => env('ANTHROPIC_API_KEY'),
    'bot_tone' => 'Friendly and encouraging',
]);
```

2. **Send message as usual:**
```php
$message = $messageService->wagerAnnouncement($wager);
$group->sendMessage($message);  // Auto-enhances if configured
```

3. **Disable for a group:**
```php
$group->update([
    'settings' => ['llm_enhancement_enabled' => false]
]);
```

That's it! The system handles the rest automatically.