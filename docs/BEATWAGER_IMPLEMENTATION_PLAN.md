# BeatWager - Revised Implementation Plan

## Overview
This document outlines the remaining features and implementation approach for BeatWager, building on the existing codebase.

## Current State Summary

### ✅ Already Implemented
- **Core Wagering System** (US-001, US-002, US-003)
  - Wager creation via `/newwager`
  - Web-based wager configuration
  - Flexible wager amounts
  - Settlement with challenger system
  
- **Leaderboard & Stats** (US-004)
  - Points tracking
  - Basic leaderboard display
  - User statistics

- **Reputation Decay** (US-007)
  - 14-day inactivity decay system
  - Minimum floor protection

- **Seasons & Reset** (US-008)
  - Annual season system
  - Points reset mechanism

- **Point Economy** (US-010)
  - Starting balance (1000 points)
  - Minimum wager (10 points)
  - Proportional distribution

- **Events System** (Partial US-013)
  - Event creation via `/newevent`
  - RSVP functionality
  - Attendance tracking
  - Bonus points for attendance

---

## Platform Abstraction Layer

### Current State
- Tightly coupled to Telegram via `TelegramWebhookController`
- Direct Bot API usage throughout

### Proposed Architecture

```php
// app/Contracts/MessagingPlatform.php
namespace App\Contracts;

interface MessagingPlatform
{
    public function sendMessage(string $chatId, string $message, array $options = []): void;
    public function sendMessageWithButtons(string $chatId, string $message, array $buttons): void;
    public function sendMessageWithInlineKeyboard(string $chatId, string $message, array $keyboard): void;
    public function editMessage(string $chatId, int $messageId, string $newText): void;
    public function deleteMessage(string $chatId, int $messageId): void;
    
    // Parse incoming webhooks into standard format
    public function parseWebhook(array $data): PlatformMessage;
}

// app/Services/Platforms/TelegramPlatform.php
class TelegramPlatform implements MessagingPlatform
{
    public function __construct(
        private BotApi $bot
    ) {}
    
    public function sendMessage(string $chatId, string $message, array $options = []): void
    {
        $this->bot->sendMessage(
            $chatId, 
            $message,
            $options['parse_mode'] ?? 'Markdown',
            $options['disable_preview'] ?? false,
            $options['reply_to'] ?? null,
            $options['reply_markup'] ?? null
        );
    }
}

// Future: DiscordPlatform, WhatsAppPlatform, etc.
```

---

## LLM Service Implementation

### Core Service

```php
// app/Services/LLMService.php
namespace App\Services;

use App\Models\Group;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LLMService
{
    private const CACHE_TTL = 3600; // 1 hour cache for similar prompts
    
    public function generateBotMessage(
        Group $group,
        string $eventType,
        array $context = []
    ): string {
        // Check if group has LLM configured
        if (!$group->llm_api_key) {
            return $this->getFallbackMessage($eventType, $context);
        }
        
        // Try cache first for common patterns
        $cacheKey = $this->getCacheKey($group->id, $eventType, $context);
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }
        
        $systemPrompt = $this->buildSystemPrompt($group);
        $userPrompt = $this->buildUserPrompt($eventType, $context);
        
        $response = $this->callLLMProvider(
            $group->llm_provider,
            $group->llm_api_key,
            $systemPrompt,
            $userPrompt
        );
        
        // Cache the response
        Cache::put($cacheKey, $response, self::CACHE_TTL);
        
        return $response;
    }
    
    private function buildSystemPrompt(Group $group): string
    {
        $groupType = $group->group_type;
        $botTone = $group->bot_tone ?? 'friendly and encouraging';
        
        return "You are BeatWager bot managing social wagers for a {$groupType} group. 
                Your responses should be concise (max 2-3 sentences) and maintain this tone: {$botTone}.
                You facilitate wagers, announce results, and keep the energy high.
                Never mention being an AI or technical details.";
    }
    
    private function buildUserPrompt(string $eventType, array $context): string
    {
        return match($eventType) {
            'wager_created' => $this->buildWagerCreatedPrompt($context),
            'wager_settled' => $this->buildWagerSettledPrompt($context),
            'event_created' => $this->buildEventCreatedPrompt($context),
            'reputation_decay' => $this->buildReputationDecayPrompt($context),
            'badge_earned' => $this->buildBadgeEarnedPrompt($context),
            default => "Announce: " . json_encode($context)
        };
    }
    
    private function callLLMProvider(
        string $provider,
        string $apiKey,
        string $systemPrompt,
        string $userPrompt
    ): string {
        return match($provider) {
            'anthropic' => $this->callAnthropic($apiKey, $systemPrompt, $userPrompt),
            'openai' => $this->callOpenAI($apiKey, $systemPrompt, $userPrompt),
            default => throw new \Exception("Unknown LLM provider: {$provider}")
        };
    }
    
    private function callAnthropic(string $apiKey, string $system, string $user): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307', // Cheap and fast
            'max_tokens' => 150,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user]
            ]
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Anthropic API error: ' . $response->body());
        }
        
        return $response->json()['content'][0]['text'];
    }
    
    // Fallback for groups without LLM
    private function getFallbackMessage(string $eventType, array $context): string
    {
        return match($eventType) {
            'wager_created' => "🎲 New wager created: {$context['title']}! Place your bets!",
            'wager_settled' => "🏆 Wager settled! {$context['winners']} won {$context['total_pot']} points!",
            'event_created' => "📅 New event: {$context['name']}! RSVP now!",
            'reputation_decay' => "📉 {$context['user']}'s points decayed to {$context['new_balance']}!",
            'badge_earned' => "🏅 {$context['user']} earned the {$context['badge']} badge!",
            default => "Something happened in the group!"
        };
    }
}
```

---

## Remaining Features Priority List

### Phase 1: Core Enhancements (Week 1-2)

#### 1. Bot Personality System (US-005)
- ✅ Database fields added (llm_api_key, bot_tone, group_type)
- [ ] LLM Service implementation
- [ ] Integration with existing message points
- [ ] Admin UI for configuration

#### 2. Badges System (US-009)
```php
// app/Services/BadgeService.php
class BadgeService
{
    private const BADGES = [
        'oracle' => ['name' => 'The Oracle', 'criteria' => 'highest_win_rate'],
        'degen' => ['name' => 'The Degen', 'criteria' => 'most_wagers_created'],
        'shark' => ['name' => 'The Shark', 'criteria' => 'highest_net_gain'],
        'loyalist' => ['name' => 'The Loyalist', 'criteria' => 'highest_participation'],
        'referee' => ['name' => 'The Referee', 'criteria' => 'most_accurate_settlements'],
        'ghost' => ['name' => 'The Ghost', 'criteria' => 'rsvp_no_show_ratio'],
    ];
    
    public function calculateBadges(Group $group): void
    {
        // Run weekly via scheduled job
    }
}
```

#### 3. Grudge Memory (US-006)
```php
// app/Services/GrudgeService.php
class GrudgeService
{
    public function getRecentHistory(User $user1, User $user2, Group $group): array
    {
        return Wager::where('group_id', $group->id)
            ->whereHas('entries', fn($q) => $q->where('user_id', $user1->id))
            ->whereHas('entries', fn($q) => $q->where('user_id', $user2->id))
            ->where('status', 'settled')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($w) => $this->formatGrudgeContext($w, $user1, $user2));
    }
}
```

### Phase 2: Engagement Features (Week 3-4)

#### 4. Revenge Bets (US-012)
```php
// app/Jobs/OfferRevengeBet.php
class OfferRevengeBet implements ShouldQueue
{
    public function handle(): void
    {
        // Check for recent big losses (>100 points)
        // Create auto-generated revenge bet
        // Send targeted message to loser
    }
}
```

#### 5. Weekly Recaps (US-015)
```php
// app/Jobs/GenerateWeeklyRecap.php
class GenerateWeeklyRecap implements ShouldQueue
{
    public function handle(): void
    {
        // Aggregate weekly stats
        // Generate engaging summary
        // Post to all active groups
    }
}
```

#### 6. Bailout System (US-011)
```php
// app/Models/BailoutAssignment.php
class BailoutAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'group_id',
        'bailout_number',
        'assignment_type',
        'assignment_details',
        'status',
        'expires_at',
    ];
}
```

### Phase 3: Platform Expansion (Week 5-6)

#### 7. Platform Abstraction
- [ ] Implement MessagingPlatform interface
- [ ] Refactor TelegramWebhookController to use abstraction
- [ ] Add Discord support
- [ ] Add WhatsApp Business API support

#### 8. Long-tail Bets (US-014)
- [ ] Add reminder system for long-dated wagers
- [ ] Scheduled jobs for notifications
- [ ] Special UI treatment for long bets

---

## Database Additions Needed

```sql
-- Badges table
CREATE TABLE badges (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    group_id UUID REFERENCES groups(id),
    badge_type VARCHAR(50),
    earned_at TIMESTAMP,
    season_id UUID,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Bailout assignments
CREATE TABLE bailout_assignments (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    group_id UUID REFERENCES groups(id),
    bailout_number INTEGER,
    assignment_type VARCHAR(50),
    assignment_details JSON,
    status VARCHAR(20),
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Add to groups table (already done in migration)
-- llm_api_key, llm_provider, bot_tone, group_type, settings
```

---

## Implementation Timeline

### Week 1-2: Foundation
- [x] Database migrations for LLM settings
- [ ] LLM Service implementation
- [ ] Badge calculation system
- [ ] Basic grudge memory

### Week 3-4: Engagement
- [ ] Revenge bet system
- [ ] Weekly recap generation
- [ ] Bailout assignments
- [ ] Enhanced notifications

### Week 5-6: Platform & Polish
- [ ] Platform abstraction layer
- [ ] Discord integration
- [ ] Long-tail bet reminders
- [ ] Admin dashboard improvements

---

## Configuration Examples

### Group Settings
```php
$group->update([
    'llm_api_key' => 'sk-ant-...',
    'llm_provider' => 'anthropic',
    'bot_tone' => 'Sarcastic sports commentator who makes everything dramatic',
    'group_type' => 'friends',
    'settings' => [
        'revenge_bets_enabled' => true,
        'weekly_recaps_enabled' => true,
        'announcement_frequency' => 'major_events_only',
    ]
]);
```

### LLM Prompts
```php
// Wager Settlement
$context = [
    'wager_title' => 'Will John finish his marathon?',
    'winners' => ['Sarah', 'Mike'],
    'losers' => ['Alex', 'Emma'],
    'total_pot' => 450,
    'biggest_winner' => ['name' => 'Sarah', 'amount' => 280],
    'biggest_loser' => ['name' => 'Alex', 'amount' => 200],
    'grudge_context' => 'This is the 3rd time Sarah has beaten Alex this month',
];

// Generated message (with sarcastic sports commentator tone):
// "🎯 AND IT'S OVER! Sarah demolishes Alex AGAIN - that's 3 in a row! 
//  Sarah pockets 280 points while Alex's wallet weeps. Total carnage: 450 points redistributed!"
```

---

## Next Steps

1. **Update Group model** with new fields
2. **Create LLM Service** with provider abstraction
3. **Add Badge calculation job** to run weekly
4. **Integrate LLM responses** into existing message points
5. **Build admin UI** for group configuration

The key is to start with the LLM service and bot personality since it enhances every interaction, then layer on the engagement features that create memorable moments and drive retention.