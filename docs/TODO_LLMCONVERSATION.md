# Future Features - LLM Conversation

This document tracks feature ideas and implementation approaches discussed with Claude.

## Topic 2: Post-Event Check-in Message ("Hangover Check")

**Date Discussed**: 2025-11-15
**Status**: Ideation
**Priority**: Low (Nice-to-have)

### Concept
Send a friendly check-in message at 9am the day after an event to spark conversation and engagement.

### User Story
> "We had an event yesterday, a few of us were having a hard time this morning and all day due to that event (hangover). Now while we can't assume all events to be alcohol related, I think it'd be fun to schedule a message at 9am the day following an event with 'How's everyone doing after that event?' Just to spark conversation."

### Proposed Approach

**High-Level Flow**:
1. Scheduled job runs daily at 9am
2. Finds completed events from previous day
3. Sends casual check-in message to each event's group
4. Message tone: Friendly, conversational, non-intrusive

**Technical Implementation**:
1. Create `App\Console\Commands\SendEventFollowUpCommand`
2. Schedule in `app/Console/Kernel.php` → `$schedule->command('events:send-followup')->dailyAt('09:00')`
3. Query logic:
   ```php
   GroupEvent::where('event_date', '>=', now()->subDay()->startOfDay())
       ->where('event_date', '<', now()->subDay()->endOfDay())
       ->where('status', 'completed')
       ->whereNull('follow_up_sent_at') // Prevent duplicates
       ->get();
   ```
4. Message format: `"How's everyone doing after {event_name}? 🤕☕"`
5. Send via `MessengerAdapterInterface::sendMessage()` to group
6. Track with new column: `follow_up_sent_at` timestamp on `group_events` table

**Edge Cases to Consider**:
- Multi-day events: Only send after final day
- Cancelled events: Skip (already filtered by status='completed')
- Timezone: Use server time (globally applied)
- Opt-in/opt-out: ✅ **DECISION**: Enabled globally (not too invasive, fits bot purpose)
- RSVP consideration: ✅ **DECISION**: Skip events with no RSVPs (event shouldn't be processable anyway)
- Message tone: ✅ **DECISION**: Keep general/casual tone - we only have time and title, not enough context for tone variation

**Database Changes Required**:
```php
// Migration: add_follow_up_sent_at_to_group_events
Schema::table('group_events', function (Blueprint $table) {
    $table->timestamp('follow_up_sent_at')->nullable()->after('attendance_prompt_sent_at');
});
```

**Configuration Considerations**:
- Time (9am vs customizable)
- Message template (hardcoded vs configurable)
- Feature toggle (global enable/disable)
- Per-group settings (future enhancement)

---

## Topic 3: Direct Bot Mentions (@WagerBot) with LLM Integration

**Date Discussed**: 2025-11-15
**Status**: Ideation
**Priority**: High (User Experience Enhancement + Engagement Driver)
**Decision**: ✅ **LLM Integration** for broader conversational capabilities

### Concept
Allow group members to directly address the bot with natural language for both command execution AND general conversation/assistance.

### User Story
> "I've been wondering if we should allow group members to directly address the bot. The bot has access to all messages. I tried e.g. '@WagerBot How are you?' and it sees the message but doesn't respond. I'd assume we could follow a logic where we listen to @WagerBot, no command before it."

### Current Behavior
Bot receives all messages but only processes slash commands. Messages like:
- `@WagerBot How are you?` → Ignored
- `@WagerBot help` → Ignored
- `@WagerBot Do you know anything against this hangover?` → Ignored
- `@WagerBot Any suggestions for a fun next wager?` → Ignored
- `/login@WagerBot` → Processed ✓

### Expanded Use Cases Beyond Commands
**Conversational Queries**:
- "Do you know anything against this hangover @WagerBot?" → Hangover tips/remedies
- "Any suggestions for a fun next wager?" → Wager idea generation
- "What's the weather like?" → General knowledge (if desired)
- "Explain how challenges work" → Feature explanation
- "Who's winning the most wagers?" → Stats/leaderboard insights

**Command Mapping**:
- "@WagerBot what's my balance?" → Execute `/balance`
- "@WagerBot show active wagers" → Execute `/wagers`
- "@WagerBot help me" → Execute `/help`

### Proposed Approaches

#### **Option A: Simple Pattern Matching** (Lightweight)
**Implementation**:
- Detect messages containing `@WagerBot` or bot mention entity
- Parse for keywords:
  - `help` → Show command list
  - `balance` → Execute BalanceCommandHandler
  - `wagers|challenges|events` → Show active items
  - Default → "I'm here to help! Try /help to see what I can do"

**Pros**:
- Simple implementation
- No external dependencies
- Predictable behavior
- Low latency

**Cons**:
- Limited flexibility
- Requires hardcoding patterns
- Poor handling of complex queries

#### **Option B: LLM Integration** (Sophisticated)
**Implementation**:
- Send mention text to OpenAI/Anthropic API
- Prompt: "User said: '{message}'. Map this to a command: /balance, /wagers, /help, etc."
- Execute corresponding CommandHandler
- Respond in natural language

**Pros**:
- Natural conversation
- Handles complex queries
- Delightful user experience
- Flexible intent recognition

**Cons**:
- External API cost ($$$)
- Latency (network round-trip)
- API dependency (failure point)
- Requires prompt engineering
- Potential for misinterpretation

#### **Option C: Hybrid Approach** (Recommended)
**Implementation Phase 1** (Now):
- Detect `@WagerBot` mentions
- Respond with acknowledgment: "Hey! I respond to commands. Try /help to see what I can do."
- Log all mentions to database for analysis

**Implementation Phase 2** (After Data Collection):
- Analyze logs to identify common patterns
- Add pattern matching for top 5-10 requests
- Example patterns discovered: "what's my balance", "show wagers", "list events"

**Implementation Phase 3** (If Justified):
- Evaluate LLM integration based on:
  - Usage volume (mentions per day)
  - Pattern complexity (can't solve with regex)
  - User feedback (frustration vs satisfaction)
  - Budget (API costs vs value)

**Pros**:
- Iterative, low-risk approach
- Data-driven decisions
- Immediate user feedback
- Scalable complexity

**Cons**:
- Initial experience less impressive
- Requires patience to gather data
- Multiple implementation phases

---

## ✅ Selected Approach: LLM Integration (Option B with Enhancements)

**Rationale**: User wants broader conversational capabilities beyond command mapping (hangover tips, wager suggestions, general assistance). This requires LLM understanding and generation.

### LLM Implementation Architecture

**Provider Options**:
1. **OpenAI** (GPT-4o-mini): Fast, affordable (~$0.15/1M input tokens, ~$0.60/1M output tokens)
2. **Anthropic** (Claude 3.5 Haiku): Very fast, competitive pricing (~$0.80/1M input, ~$4/1M output)
3. **OpenRouter**: Multi-provider aggregation with fallbacks

**Recommended**: Start with **OpenAI GPT-4o-mini** for cost efficiency and speed.

### System Architecture

```
User Message → TelegramMessageHandler
    ↓
Detect @WagerBot mention?
    ↓ Yes
BotMentionHandler
    ↓
Extract mention text → LLM Service
    ↓
LLM analyzes intent:
    1. Command mapping? → Execute CommandHandler → Respond
    2. Conversational? → Generate response → Send to group
    3. Contextual query? → Fetch data → Generate response → Send
```

### LLM Service Design

**Core Responsibilities**:
1. Intent classification (command vs conversation)
2. Command extraction and mapping
3. Context-aware response generation
4. Tool/function calling for data access

**Service Interface**:
```php
interface LLMServiceInterface
{
    public function processUserMessage(
        string $message,
        User $user,
        Group $group,
        array $context = []
    ): BotResponse;
}

class BotResponse
{
    public function __construct(
        public readonly BotResponseType $type,
        public readonly ?string $text = null,
        public readonly ?string $commandToExecute = null,
        public readonly array $metadata = []
    ) {}
}

enum BotResponseType: string
{
    case COMMAND = 'command';           // Execute a slash command
    case CONVERSATIONAL = 'conversational'; // Free-form response
    case DATA_QUERY = 'data_query';     // Requires data fetch + response
    case UNKNOWN = 'unknown';           // Couldn't understand
}
```

### Implementation Phases

#### **Phase 1: Basic LLM Integration** (MVP)
**Scope**: Command mapping + simple conversational responses

**Features**:
- Detect @WagerBot mentions
- Send to LLM with system prompt
- Map to commands OR generate friendly response
- No data access yet (no user balance, wagers, etc.)

**System Prompt**:
```
You are WagerBot, a friendly assistant for a group wagering/betting system.

Users can ask you to:
1. Execute commands like checking balance, viewing wagers, getting help
2. General questions about hangover remedies, wager ideas, etc.

Available commands you can trigger:
- /balance - Show user's point balance
- /wagers - Show active wagers
- /challenges - Show active challenges
- /events - Show upcoming events
- /help - Show all commands
- /leaderboard - Show group rankings

If the user asks something that maps to a command, respond with:
COMMAND: /balance

If it's a general question, provide a helpful, friendly response in 1-2 sentences.

Keep responses casual, fun, and brief (max 100 words).
```

**Cost Estimate**:
- Average message: ~100 input tokens + ~50 output tokens
- Cost per interaction: ~$0.00003 (300 tokens total)
- 1000 interactions/month: ~$0.03
- Very affordable ✅

**Code Structure**:
```php
// app/Services/LLM/OpenAILLMService.php
class OpenAILLMService implements LLMServiceInterface
{
    public function __construct(
        private readonly Client $client, // OpenAI PHP SDK
        private readonly string $model = 'gpt-4o-mini'
    ) {}

    public function processUserMessage(
        string $message,
        User $user,
        Group $group,
        array $context = []
    ): BotResponse {
        $systemPrompt = $this->buildSystemPrompt($group);
        $userPrompt = $this->buildUserPrompt($message, $user, $context);

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);

        return $this->parseResponse($response);
    }
}
```

#### **Phase 2: Function Calling / Tool Use** (Enhanced)
**Scope**: LLM can access live data from database

**New Capabilities**:
- "Who's winning?" → Fetch leaderboard data → Generate natural response
- "What wagers are active?" → Fetch wagers → Summarize naturally
- "What's my balance?" → Fetch user balance → Respond with context

**Implementation**: Use OpenAI function calling or Anthropic tool use
```php
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_user_balance',
            'description' => 'Get the current point balance for a user in a group',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'user_id' => ['type' => 'string'],
                    'group_id' => ['type' => 'string'],
                ],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_active_wagers',
            'description' => 'Get list of active wagers in a group',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'group_id' => ['type' => 'string'],
                    'limit' => ['type' => 'integer', 'default' => 5],
                ],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'get_leaderboard',
            'description' => 'Get top users by points in a group',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'group_id' => ['type' => 'string'],
                    'limit' => ['type' => 'integer', 'default' => 5],
                ],
            ],
        ],
    ],
];
```

#### **Phase 3: Conversation Memory** (Advanced)
**Scope**: Multi-turn conversations with context retention

**Features**:
- Remember recent conversation history
- Reference previous questions
- More natural dialogue flow

**Implementation**: Store last N messages per user/group in Redis or cache

### Safety & Moderation

**Rate Limiting**:
- Max 10 LLM calls per user per hour (prevent spam/abuse)
- Max 50 LLM calls per group per hour

**Content Filtering**:
- Pre-filter offensive input before sending to LLM
- Post-filter LLM responses for inappropriate content
- Log all interactions for review

**Fallback Handling**:
```php
try {
    $response = $this->llmService->processUserMessage(...);
} catch (LLMServiceException $e) {
    // Fallback to simple pattern matching or error message
    Log::error('LLM service failed', ['error' => $e->getMessage()]);
    return "Sorry, I'm having trouble understanding right now. Try /help to see available commands.";
}
```

### Configuration

**Environment Variables**:
```env
LLM_PROVIDER=openai  # openai, anthropic, openrouter
LLM_MODEL=gpt-4o-mini
LLM_API_KEY=sk-...
LLM_MAX_TOKENS=150
LLM_TEMPERATURE=0.7
LLM_RATE_LIMIT_PER_USER=10  # per hour
LLM_RATE_LIMIT_PER_GROUP=50  # per hour
LLM_ENABLED=true
```

### Monitoring & Analytics

**Track**:
- Total LLM calls per day/month
- Cost per call and total monthly cost
- Response time (p50, p95, p99)
- Intent distribution (command vs conversational)
- User satisfaction (via reactions? optional)
- Error rate and types

**Dashboard Metrics**:
```php
// LLM usage stats
Schema::create('llm_interactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('group_id')->nullable()->constrained();
    $table->text('user_message');
    $table->string('detected_intent'); // command, conversational, data_query
    $table->text('bot_response')->nullable();
    $table->string('command_executed')->nullable();
    $table->integer('input_tokens')->nullable();
    $table->integer('output_tokens')->nullable();
    $table->decimal('cost', 10, 6)->nullable(); // Track per-interaction cost
    $table->integer('response_time_ms')->nullable();
    $table->string('status'); // success, error, rate_limited
    $table->timestamps();
});
```

### Testing Strategy

**Unit Tests**:
- Mock LLM responses
- Test intent classification
- Test command mapping
- Test error handling

**Integration Tests**:
- Test with real LLM API (in test environment)
- Verify function calling works
- Test rate limiting

**Manual Testing**:
- Create test group
- Test various queries
- Verify responses are appropriate
- Test edge cases

### Rollout Plan

1. **Development**: Implement Phase 1 (command mapping + basic conversation)
2. **Internal Testing**: Test with small group (your friend group)
3. **Beta**: Enable for 1-2 groups, gather feedback
4. **Refinement**: Adjust prompts based on real usage
5. **Full Rollout**: Enable globally with feature flag
6. **Phase 2**: Add function calling after validating Phase 1
7. **Phase 3**: Add conversation memory if needed

---

## Original Hybrid Approach (Not Selected)

### ~~Recommended Implementation (Option C - Hybrid)~~ (Archived)

**Step 1: Detection & Acknowledgment**
```php
// In TelegramMessageHandler or new BotMentionHandler
if ($this->isBotMentioned($message)) {
    $this->messenger->sendMessage(
        OutgoingMessage::text(
            $message->chatId,
            "👋 Hey! I respond to commands like /help, /wagers, /balance. Try /help to see everything I can do!"
        )
    );
    $this->logBotMention($message); // For analysis
}
```

**Step 2: Analytics Table**
```php
// Migration: create_bot_mentions_table
Schema::create('bot_mentions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('group_id')->nullable()->constrained();
    $table->text('message_text');
    $table->string('detected_intent')->nullable(); // For phase 2
    $table->boolean('handled')->default(false);
    $table->timestamps();
});
```

**Step 3: Pattern Recognition (Future)**
After collecting data, analyze with:
```sql
SELECT message_text, COUNT(*) as frequency
FROM bot_mentions
WHERE handled = false
GROUP BY message_text
ORDER BY frequency DESC
LIMIT 20;
```

**Detection Logic**:
```php
private function isBotMentioned(IncomingMessage $message): bool
{
    $botUsername = config('telegram.bot_username', 'WagerBot');

    // Check message text for @mention
    if (str_contains($message->text, '@' . $botUsername)) {
        return true;
    }

    // Check Telegram entities for mention
    if ($message->entities) {
        foreach ($message->entities as $entity) {
            if ($entity['type'] === 'mention' && $entity['user'] === $botUsername) {
                return true;
            }
        }
    }

    return false;
}
```

### Next Steps
1. Implement Option C Phase 1 (detection + acknowledgment + logging)
2. Monitor usage for 2-4 weeks
3. Analyze logs to identify patterns
4. Decide: Pattern matching vs LLM based on data
5. Implement Phase 2 based on findings

---

---

## Implementation Priority & Next Steps

1. **Topic 1**: ✅ **COMPLETED** - Fixed GroupEvent 'wager' relationship error in DashboardController
   - Fixed polymorphic eager loading to only load 'wager' on WagerEntry, not GroupEvent
   - Error resolved, login flow now works

2. **Topic 3**: 🚀 **HIGH PRIORITY** - LLM Integration for @WagerBot mentions
   - Start with Phase 1: Basic command mapping + conversational responses
   - Estimated effort: 2-3 days development + 1 week testing
   - Low ongoing cost (~$0.03 per 1000 interactions)
   - High engagement value

3. **Topic 2**: 📅 **MEDIUM PRIORITY** - Post-event check-in messages
   - Straightforward scheduled command implementation
   - Estimated effort: 1 day development
   - Zero ongoing cost
   - Nice engagement feature

**Recommended Implementation Order**:
1. Topic 3 Phase 1 (LLM basics) → Test with real users → Gather feedback
2. Topic 2 (Event follow-ups) → Easy win while collecting LLM usage data
3. Topic 3 Phase 2 (Function calling) → After validating Phase 1 works well
