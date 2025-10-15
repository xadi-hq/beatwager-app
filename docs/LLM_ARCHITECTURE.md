# BeatWager LLM Architecture - Final Implementation

## Overview

Clean, LLM-first message generation with structured fallbacks. No legacy constraints, built for extensibility.

## Architecture Flow

```
MessageService (creates MessageContext)
    ↓
ContentGenerator (tries LLM, falls back)
    ↓
    ├─ LLMService (if group configured)
    │   ├─ Cache check
    │   ├─ Build prompts from structured metadata
    │   ├─ Call Anthropic/OpenAI
    │   └─ Validate & cache response
    │
    └─ FallbackContentGenerator (always works)
        ├─ Load structured metadata from lang
        ├─ Interpolate template
        └─ Return formatted message
    ↓
Message DTO (with content ready)
    ↓
Group.sendMessage()
    ↓
MessengerFactory (platform-specific sending)
```

## Key Components

### 1. MessageContext DTO

Structured context for message generation:

```php
new MessageContext(
    key: 'wager.announced',
    intent: 'Announce new wager and drive participation',
    requiredFields: ['title', 'type', 'stake', 'deadline'],
    data: [
        'title' => 'Will John finish marathon?',
        'stake' => 50,
        'deadline' => 'Oct 20, 2025',
        'creator' => 'Sarah',
    ],
    group: $group
)
```

### 2. Structured Lang Metadata

```php
// lang/en/messages.php
'wager' => [
    'announced' => [
        'intent' => 'Announce a newly created wager and drive participation',
        'required_fields' => ['title', 'type', 'stake', 'deadline'],
        'fallback_template' => "🎯 New Wager: {title}...",
        'tone_hints' => ['exciting', 'call_to_action'],
    ],
]
```

### 3. ContentGenerator

Unified generation with LLM-first approach:

```php
class ContentGenerator
{
    public function generate(MessageContext $ctx, ?Group $group): string
    {
        // Try LLM if configured
        if ($group?->llm_api_key) {
            try {
                return $this->llm->generate($ctx, $group);
            } catch (\Throwable $e) {
                Log::warning('LLM failed, using fallback');
            }
        }
        
        // Always has fallback
        return $this->fallback->generate($ctx);
    }
}
```

### 4. LLMService

Handles API calls with caching:

```php
- Builds system prompt from group tone + type
- Builds user prompt from context intent + data
- Supports Anthropic (claude-haiku) and OpenAI (gpt-3.5-turbo)
- 1-hour cache on successful responses
- 10-second timeout
- Validates response length
```

### 5. FallbackContentGenerator

Template-based generation:

```php
- Loads metadata from lang files
- Interpolates {placeholders} with context data
- Flattens nested data structures
- Always works, even if lang file missing
```

## Usage Examples

### Basic Usage (No LLM)

```php
$messageService = app(MessageService::class);
$message = $messageService->wagerAnnouncement($wager);
$wager->group->sendMessage($message);

// Output: "🎯 New Wager Created! Question: Will John..."
```

### With LLM Configured

```php
// Configure group
$group->update([
    'llm_api_key' => env('ANTHROPIC_API_KEY'),
    'llm_provider' => 'anthropic',
    'bot_tone' => 'Sarcastic sports commentator',
    'group_type' => 'friends',
]);

// Same code, different output
$message = $messageService->wagerAnnouncement($wager);
$wager->group->sendMessage($message);

// Output: "🏃‍♂️ BREAKING: John thinks he can finish a marathon! 
//         Sarah's calling BS with 50 points. Oct 20 deadline. 
//         Place your bets folks! 🎯"
```

### Error Handling

```php
// LLM fails? → Automatic fallback to template
// No internet? → Uses template
// Invalid API key? → Uses template  
// Timeout? → Uses template

// Messages ALWAYS send
```

## Group Configuration

### Database Fields

```sql
groups table:
- llm_api_key (text, encrypted)
- llm_provider (string: 'anthropic', 'openai')
- bot_tone (text: free-form personality description)
- group_type (enum: 'friends', 'family', 'colleagues', 'other')
- settings (json: additional config)
```

### Configuration Examples

```php
// Sarcastic friend group
$group->update([
    'llm_provider' => 'anthropic',
    'llm_api_key' => 'sk-ant-...',
    'bot_tone' => 'Sarcastic roast master who makes fun of everyone',
    'group_type' => 'friends',
]);

// Professional colleagues
$group->update([
    'llm_provider' => 'openai',
    'llm_api_key' => 'sk-...',
    'bot_tone' => 'Professional but witty, like a game show host',
    'group_type' => 'colleagues',
]);

// Disable LLM (use templates only)
$group->update([
    'llm_api_key' => null,
]);
```

## Adding New Message Types

### 1. Add to lang/messages.php

```php
'event' => [
    'announced' => [
        'intent' => 'Announce new event and encourage RSVPs',
        'required_fields' => ['name', 'date', 'location'],
        'fallback_template' => "📅 New Event: {name}\nDate: {date}\nLocation: {location}",
        'tone_hints' => ['exciting', 'social'],
    ],
],
```

### 2. Add method to MessageService

```php
public function eventAnnouncement(GroupEvent $event): Message
{
    $meta = __('messages.event.announced');
    
    $ctx = new MessageContext(
        key: 'event.announced',
        intent: $meta['intent'],
        requiredFields: $meta['required_fields'],
        data: [
            'name' => $event->name,
            'date' => $event->event_date->format('M j, Y'),
            'location' => $event->location ?? 'TBD',
        ],
        group: $event->group
    );
    
    $content = $this->contentGenerator->generate($ctx, $event->group);
    
    return new Message(
        content: $content,
        type: MessageType::Announcement,
        variables: [],
        buttons: $this->buildEventButtons($event),
        context: $event,
    );
}
```

### 3. Use it

```php
$message = $messageService->eventAnnouncement($event);
$event->group->sendMessage($message);
```

Done! LLM will automatically enhance if configured.

## Cost Optimization

### Built-in Optimizations

1. **Caching**: 1-hour cache on LLM responses (same context = cached)
2. **Cheap Models**: Uses claude-haiku ($0.25/1M) or gpt-3.5-turbo ($0.50/1M)
3. **Short Responses**: Max 300 tokens per message (~$0.0001 per message)
4. **Fallbacks**: Failed calls don't retry (instant fallback)
5. **Per-Group**: Groups without API keys = $0 cost

### Estimated Costs

```
10 messages/day × 30 days × 300 tokens × $0.25/1M = $0.00225/month/group

For 100 active groups: ~$0.23/month
```

## Testing

### Test Fallback

```php
// No LLM configured
$group->update(['llm_api_key' => null]);

$message = $messageService->wagerAnnouncement($wager);
$this->assertStringContainsString('🎯 New Wager Created', $message->content);
```

### Test LLM

```php
// Mock LLM service
$mockLLM = Mockery::mock(LLMService::class);
$mockLLM->shouldReceive('generate')
    ->andReturn('Custom LLM response');

$generator = new ContentGenerator($mockLLM, $fallback);
$content = $generator->generate($ctx, $group);

$this->assertEquals('Custom LLM response', $content);
```

### Test Cache

```php
Cache::flush();

$content1 = $llmService->generate($ctx, $group);
$content2 = $llmService->generate($ctx, $group); // Should be cached

$this->assertEquals($content1, $content2);
```

## Monitoring

### Key Metrics

```php
Log::info('LLM generation', [
    'group_id' => $group->id,
    'message_key' => $ctx->key,
    'provider' => $group->llm_provider,
    'cached' => $fromCache,
    'duration_ms' => $duration,
]);
```

Track:
- LLM success rate
- Fallback frequency  
- Cache hit rate
- Average response time
- Cost per group

## Migration Steps

1. ✅ Run migration: `php artisan migrate`
2. ✅ Services are auto-injected via DI
3. ✅ Existing code works (fallback templates)
4. Configure groups with LLM keys (optional)
5. Test with one group first
6. Roll out to more groups

## Next Phase Features

When ready to enhance:
- **Grudge Memory**: Add recent bet history to context
- **Leaderboard Context**: Include current rankings
- **Badge References**: Mention user badges in messages
- **Time-based Tones**: Different tone for late night vs morning
- **Reaction Analysis**: Track which messages get more engagement

---

## Quick Reference

### Enable LLM for Group

```php
$group->update([
    'llm_api_key' => env('ANTHROPIC_API_KEY'),
    'bot_tone' => 'Your desired personality here',
]);
```

### Disable LLM for Group

```php
$group->update(['llm_api_key' => null]);
```

### Add New Message Type

1. Add to `lang/en/messages.php`
2. Add method to `MessageService`
3. Call it: `$messageService->yourMethod($model)`

### Debug LLM

```bash
tail -f storage/logs/laravel.log | grep "LLM"
```

That's it! Clean, extensible, always works.