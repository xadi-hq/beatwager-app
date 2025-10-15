# BetWager Bot - Product Requirements Document

## Vision
A platform-agnostic chat bot that enables friend groups to create and settle social wagers using points. The bot acts as a character in the group chat, facilitating friendly competition, maintaining leaderboards, and creating memorable moments through customizable personality and social dynamics.

---

## Core Principles
- **Fun First**: This is about entertainment and social bonding, not gambling
- **Low Barrier**: Easy to create bets, easy to participate
- **Sticky Engagement**: Multiple touch points for different participation levels
- **Platform Agnostic**: Should work across Telegram, Discord, WhatsApp, etc.
- **Simple Implementation**: No ML/complex algorithms - focus on clever application of simple mechanics

---

## User Stories

### Tier 1: MVP Features (Must-Have)

#### US-001: Bet Creation
**As a user**, I want to create a custom wager so that I can engage my friends in predictions about our social lives.

**Acceptance Criteria:**
- User initiates bet creation via `/newwager` command
- Bot provides a URL to web app for bet configuration
- User can set:
  - Question/prompt (text)
  - Options (binary or multiple choice)
  - Resolution date/time
  - Category/tags (optional)
- Bet is posted back to chat with summary and participation link
- Custom wager amounts per user (no fixed amounts)

#### US-002: Bet Participation
**As a user**, I want to place a wager on an existing bet so that I can compete with my friends.

**Acceptance Criteria:**
- User can click bet link to view details and place wager
- User can wager any amount within their point balance (minimum: 10 points)
- User sees current odds/distribution (who bet what)
- Wagers are locked once placed (no changing)
- Bot announces new wagers in chat (optional setting)

#### US-003: Bet Settlement
**As a user**, I want to settle a completed bet so that points can be distributed.

**Acceptance Criteria:**
- Any user can mark a bet as settled and declare outcome
- Settler is recorded in the system
- Other users have 24-hour challenge window
- If challenged, bet goes to group vote (simple majority)
- Points are distributed to winners proportionally to their wagers
- Bot announces settlement with drama/personality
- Track who settles most bets (badge opportunity)

#### US-004: Leaderboard & Stats
**As a user**, I want to see my ranking and stats so that I can track my performance.

**Acceptance Criteria:**
- Persistent leaderboard showing:
  - Current points ranking
  - Lifetime stats (win rate, total bets, biggest win/loss)
  - Current season stats
  - Active streaks
- Accessible via `/leaderboard` command or web view
- Updates in real-time after bet settlement
- Shows badges/titles next to names

#### US-005: Bot Personality (Group-Level)
**As a group admin**, I want to set the bot's personality so that it matches our group's vibe.

**Acceptance Criteria:**
- Group settings allow choosing bot personality preset:
  - Roast Master (sarcastic, taunting)
  - Hype Man (enthusiastic, encouraging)
  - Sports Commentator (play-by-play style)
  - Shakespearean (theatrical, dramatic)
  - Custom (user-provided prompt)
- Bot uses this personality in all interactions:
  - Bet announcements
  - Settlement messages
  - Leaderboard updates
  - Grudge references
- Personality can be updated by group admin

#### US-006: Grudge Memory
**As a user**, I want the bot to remember and reference past interactions so that there's ongoing narrative.

**Acceptance Criteria:**
- Bot tracks recent bet history between specific users (last 10 interactions)
- References patterns in its messages:
  - "John betting against Mike again... that's 3 losses in a row"
  - "Sarah's on a revenge tour after last week"
- Includes grudge references in settlement announcements
- No complex ML - just query recent settled bets for user pairs

#### US-007: Reputation Decay
**As a user**, I want inactive players to lose points so that participation is encouraged.

**Acceptance Criteria:**
- Users who don't place a wager for 14 consecutive days lose 5% of points
- Minimum floor: 50 points (can't decay below this)
- Bot publicly announces decay: "Mike's empire is crumbling... down to 847 points"
- Decay pauses if user has no pending bets to participate in
- Decay resets when user places any wager

#### US-008: Seasons & Annual Reset
**As a user**, I want seasons to reset annually so that there are fresh starts and milestones.

**Acceptance Criteria:**
- Seasons run calendar year (Jan 1 - Dec 31)
- On January 1st:
  - Previous season's leaderboard archived as "Hall of Fame"
  - All user points reset to 1,000
  - Season counter increments
- Bot automatically posts "Year in Review" on Jan 1st with:
  - Season champion
  - Biggest bet won/lost
  - Most controversial bet (closest vote split)
  - Personal highlights for each active user
  - Funny/memorable moments

#### US-009: Badges & Titles
**As a user**, I want to earn badges that reflect my betting style so that I have a unique identity.

**Acceptance Criteria:**
- Badges auto-assigned based on behavior:
  - **The Oracle**: Highest win rate (min 10 bets)
  - **The Degen**: Most bets created
  - **The Shark**: Biggest net point gain this season
  - **The Optimist**: Consistently bets on positive outcomes
  - **The Pessimist**: Consistently bets on negative outcomes
  - **The Wildcard**: Most unpredictable voting pattern
  - **The Loyalist**: Highest participation rate
  - **The Referee**: Most bets settled accurately (no challenges)
  - **The Social Butterfly**: Most events hosted
  - **The Attendee**: Highest event participation
  - **The Ghost**: RSVPs yes but never shows
- Badges recalculated weekly or after significant events
- Displayed next to username in leaderboard
- Users can have multiple badges

#### US-010: Point Economy
**As a user**, I want a fair point system so that the game remains engaging.

**Acceptance Criteria:**
- All users start with 1,000 points per season
- Minimum wager: 10 points
- No maximum wager (can bet entire balance)
- Users can go to 0 points but not negative
- Winners split the pot proportionally to their wagers
- Losing wagers are removed from circulation (deflationary)

---

### Tier 2: High-Impact Features (Post-MVP)

#### US-011: Bailout System
**As a user at zero points**, I want a dramatic way to get back in the game so that I don't disengage.

**Acceptance Criteria:**
- Users who hit 0 points can request a "bailout"
- Bailout gives 200 points
- User must complete an assignment to receive bailout:
  - Assignment difficulty scales (1st bailout = easy, 3rd = hard)
  - Examples: "Create 3 new bets", "Get 5 people to participate in your next bet", "Win a bet this week"
- Maximum 3 bailouts per season
- Bot announces bailout dramatically: "Mike is requesting a bailout. Assignment: [task]. Will he survive?"
- If user declines/fails assignment, they can only make minimum wagers (10 points) until season reset

#### US-012: Revenge Bets
**As a user who just lost big**, I want a chance to win it back so that there's drama.

**Acceptance Criteria:**
- Triggered automatically when user loses 100+ points in single bet
- Bot offers 24-hour window: "Mike just lost 150 points. Revenge bet available!"
- Revenge bet is double-or-nothing at the lost amount
- Revenge bet is auto-generated (bot creates topical question)
- Other users can pile on if they want
- Special formatting/highlighting in chat
- Only one revenge bet active per user at a time

#### US-013: Events System
**As a user**, I want to create events so that we can coordinate and bet on gatherings.

**Acceptance Criteria:**
- User creates event via `/newevent` command
- Event includes:
  - Title, date/time, location
  - RSVP options (yes/no/maybe)
  - Auto-generated bet suggestions:
    - "Who will actually show up?" (multi-select)
    - "How many no-shows?" (numeric range)
    - "Who arrives last?" (single select)
- Event creator can accept/modify suggested bets
- Events displayed in special calendar view
- Bot sends reminders as event approaches
- Post-event recap includes bet settlements

#### US-014: Long-Tail Bets
**As a user**, I want to create bets that resolve far in the future so that we have ongoing storylines.

**Acceptance Criteria:**
- Bets can have resolution dates months/years out
- Bot periodically reminds group of pending long-tail bets:
  - 1 month before: "Reminder: Alex's novel bet resolves in 1 month"
  - 1 week before: "Final week for Alex's novel bet!"
- Long-tail bets highlighted in special section of leaderboard
- These bets create "anchors" that keep people engaged long-term

#### US-015: Weekly Recaps
**As a user**, I want automatic summaries so that I can relive the best moments.

**Acceptance Criteria:**
- Bot generates weekly recap every Monday:
  - Top performers that week
  - Biggest upset
  - Funniest bet
  - Most active bet (most participants)
  - Leaderboard changes
- Recap is shareable outside group (recruitment tool)
- Format is engaging and visual (could be image/infographic)

---

## Technical Architecture

### System Components

```
┌─────────────────┐
│  Chat Platform  │ (Telegram/Discord/WhatsApp)
│   (Bot Client)  │
└────────┬────────┘
         │
         ├── Webhook/Polling
         │
┌────────▼────────┐
│   API Gateway   │
│  (Laravel API)  │
└────────┬────────┘
         │
         ├── REST Endpoints
         │
┌────────▼────────┐
│  Web App        │ (Vue 3 + Inertia)
│  (Bet Creation/ │
│   Management)   │
└─────────────────┘
         │
         │
┌────────▼────────────┐
│   Core Services     │
│  ─────────────────  │
│  • Bet Engine       │
│  • Point Economy    │
│  • User Management  │
│  • Badge System     │
│  • Event System     │
│  • LLM Integration  │
└────────┬────────────┘
         │
┌────────▼────────┐
│   Database      │
│  (PostgreSQL)   │
└─────────────────┘
```

### Data Models

#### Core Entities

**User**
```
- id
- platform_id (telegram_id, discord_id, etc)
- platform_type
- username
- current_points
- lifetime_stats (json)
- badges (json array)
- created_at
- updated_at
- last_active_at
```

**Group**
```
- id
- platform_id
- platform_type
- name
- settings (json)
  - bot_personality_preset
  - bot_custom_prompt
  - point_noun (default: "points")
  - decay_enabled
  - revenge_bets_enabled
- created_at
- updated_at
```

**Bet**
```
- id
- group_id
- creator_id
- question
- options (json array)
- bet_type (binary, multiple_choice)
- status (open, settled, challenged, cancelled)
- resolution_date
- created_at
- settled_at
- settled_by_user_id
- winning_option_id
```

**Wager**
```
- id
- bet_id
- user_id
- option_id
- amount
- created_at
```

**Event**
```
- id
- group_id
- creator_id
- title
- description
- event_date
- location
- rsvps (json)
- related_bets (array of bet_ids)
- created_at
```

**Season**
```
- id
- group_id
- season_number
- start_date
- end_date
- final_leaderboard (json)
- year_in_review (json)
```

**Badge_Assignment**
```
- id
- user_id
- badge_type
- earned_at
- season_id
```

### LLM Context Management

To make the bot feel alive and contextual, we need to pass relevant context to the LLM for each message generation:

#### Context Structure for LLM

```php
$context = [
    'group' => [
        'name' => $group->name,
        'personality' => $group->settings['bot_personality_preset'],
        'custom_prompt' => $group->settings['bot_custom_prompt'] ?? null,
    ],
    'trigger_event' => [
        'type' => 'bet_settled', // or 'new_wager', 'revenge_available', etc
        'data' => [
            'bet_question' => $bet->question,
            'winners' => $winners,
            'losers' => $losers,
            'pot_size' => $totalWagered,
        ]
    ],
    'recent_history' => [
        // Last 5-10 relevant interactions
        [
            'type' => 'bet',
            'participants' => ['John', 'Mike'],
            'outcome' => 'John won 150 points from Mike',
            'timestamp' => '2 days ago',
        ],
        // ... more history
    ],
    'user_stats' => [
        'current_speaker' => [
            'name' => 'John',
            'points' => 850,
            'badges' => ['The Oracle'],
            'streak' => 3,
        ],
        'mentioned_users' => [
            // Stats for any @mentioned users
        ]
    ],
    'current_leaderboard_top3' => [
        ['name' => 'Sarah', 'points' => 1250],
        ['name' => 'Mike', 'points' => 1100],
        ['name' => 'John', 'points' => 850],
    ],
];
```

#### LLM Message Generation Service

```php
class BotMessageService
{
    public function generateMessage(
        string $eventType,
        array $context,
        Group $group
    ): string {
        $systemPrompt = $this->buildSystemPrompt($group);
        $userPrompt = $this->buildUserPrompt($eventType, $context);
        
        $response = $this->llmClient->complete([
            'model' => 'claude-sonnet-4-5',
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'max_tokens' => 300, // Keep responses concise
        ]);
        
        return $response['content'][0]['text'];
    }
    
    private function buildSystemPrompt(Group $group): string
    {
        $personality = $group->settings['bot_personality_preset'];
        $customPrompt = $group->settings['bot_custom_prompt'];
        
        $basePrompt = "You are the BetWager bot for the group '{$group->name}'. ";
        $basePrompt .= "Your role is to facilitate social wagers, announce results, and maintain the group's leaderboard. ";
        $basePrompt .= "Keep responses under 3 sentences and maintain the group's chosen tone. ";
        
        // Add personality-specific instructions
        $basePrompt .= match($personality) {
            'roast_master' => "Be sarcastic, taunting, and playful. Roast losers gently. Celebrate winners with swagger.",
            'hype_man' => "Be enthusiastic and encouraging. Hype up every action. Use exclamation points!",
            'sports_commentator' => "Narrate like a sports announcer. Create drama and tension. Use sports metaphors.",
            'shakespearean' => "Speak in theatrical, Shakespearean style. Be dramatic and poetic.",
            'custom' => $customPrompt ?? "Be helpful and friendly.",
            default => "Be helpful and friendly with personality.",
        };
        
        return $basePrompt;
    }
    
    private function buildUserPrompt(string $eventType, array $context): string
    {
        // Build context-specific prompt based on event type
        return match($eventType) {
            'bet_settled' => $this->buildSettlementPrompt($context),
            'new_wager' => $this->buildNewWagerPrompt($context),
            'revenge_available' => $this->buildRevengePrompt($context),
            'reputation_decay' => $this->buildDecayPrompt($context),
            'leaderboard_update' => $this->buildLeaderboardPrompt($context),
            default => "Something happened: " . json_encode($context),
        };
    }
    
    private function buildSettlementPrompt(array $context): string
    {
        $prompt = "A bet has been settled!\n\n";
        $prompt .= "Question: {$context['trigger_event']['data']['bet_question']}\n";
        $prompt .= "Winners: " . implode(', ', $context['trigger_event']['data']['winners']) . "\n";
        $prompt .= "Losers: " . implode(', ', $context['trigger_event']['data']['losers']) . "\n";
        $prompt .= "Total pot: {$context['trigger_event']['data']['pot_size']} points\n\n";
        
        // Add grudge context if relevant
        if (!empty($context['recent_history'])) {
            $prompt .= "Recent history between these users:\n";
            foreach ($context['recent_history'] as $event) {
                $prompt .= "- {$event['outcome']} ({$event['timestamp']})\n";
            }
        }
        
        $prompt .= "\nAnnounce the results with personality!";
        
        return $prompt;
    }
    
    // ... other prompt builders
}
```

### Event-Driven Architecture

Key events that trigger bot messages:

1. **Bet Created** → Announcement + participation prompt
2. **Wager Placed** → Optional notification (can be noisy, make configurable)
3. **Bet Settled** → Dramatic announcement with winners/losers
4. **Bet Challenged** → Call for group vote
5. **Revenge Bet Available** → Targeted message to loser
6. **Reputation Decay** → Public shame announcement
7. **Badge Earned** → Celebration message
8. **Season Rollover** → Year in Review post
9. **Event Created** → Announcement + RSVP prompt
10. **Event Reminder** → Pre-event nudge

### Queue Architecture

```php
// Laravel Queue Jobs

// Real-time (immediate)
- SendBotMessage::class
- ProcessBetSettlement::class
- ProcessWager::class

// Scheduled (cron)
- CheckReputationDecay::class // Daily
- GenerateWeeklyRecap::class // Monday 9am
- SendEventReminders::class // Check hourly
- UpdateBadges::class // Weekly
- CheckLongTailBetReminders::class // Daily

// Delayed (after trigger)
- OfferRevengeBet::class // 1 minute after big loss
- CloseRevengeBetWindow::class // 24 hours after offer
```

### API Endpoints

```
POST   /api/bot/webhook/{platform}      # Receive messages from platform
GET    /api/groups/{id}/leaderboard     # Get current leaderboard
POST   /api/bets                        # Create new bet
GET    /api/bets/{id}                   # Get bet details
POST   /api/bets/{id}/wager             # Place wager
POST   /api/bets/{id}/settle            # Settle bet
POST   /api/bets/{id}/challenge         # Challenge settlement
POST   /api/events                      # Create event
GET    /api/events/{id}                 # Get event details
POST   /api/events/{id}/rsvp            # RSVP to event
PATCH  /api/groups/{id}/settings        # Update group settings
GET    /api/users/{id}/stats            # Get user stats
POST   /api/users/{id}/bailout          # Request bailout
```

### Platform Abstraction Layer

```php
interface BotPlatformInterface
{
    public function sendMessage(string $chatId, string $message): void;
    public function sendMessageWithButtons(string $chatId, string $message, array $buttons): void;
    public function getUser(string $platformUserId): array;
    public function getChat(string $platformChatId): array;
}

class TelegramBotPlatform implements BotPlatformInterface { }
class DiscordBotPlatform implements BotPlatformInterface { }
class WhatsAppBotPlatform implements BotPlatformInterface { }
```

---

## Implementation Phases

### Phase 1: Foundation (Weeks 1-3)
- [ ] Database schema & migrations
- [ ] User & Group models
- [ ] Basic bet creation flow
- [ ] Wagering system
- [ ] Settlement logic
- [ ] Points economy
- [ ] Platform abstraction layer
- [ ] Telegram integration

### Phase 2: Personality & Engagement (Weeks 4-5)
- [ ] LLM integration service
- [ ] Bot personality system
- [ ] Grudge memory queries
- [ ] Reputation decay job
- [ ] Badge calculation system
- [ ] Basic leaderboard

### Phase 3: Seasons & Polish (Weeks 6-7)
- [ ] Season system
- [ ] Year in Review generation
- [ ] Badge display & updates
- [ ] Web app (Vue) for bet creation
- [ ] Improved leaderboard views

### Phase 4: Advanced Features (Weeks 8-10)
- [ ] Bailout system
- [ ] Revenge bets
- [ ] Events system
- [ ] Long-tail bet reminders
- [ ] Weekly recaps
- [ ] Additional platform support (Discord)

---

## Open Questions & Decisions Needed

1. **Payout Distribution**: Should we use proportional distribution or winner-takes-all for multi-option bets?
   - Proportional feels fairer
   - Winner-takes-all is simpler and more dramatic
   
2. **Minimum Participation**: Should bets require minimum number of participants to settle?
   - Prevents 1-person "sure thing" bets
   - Could frustrate users if participation is low
   
3. **Bot Rate Limiting**: How chatty should the bot be?
   - Every wager announced vs only major events?
   - Make configurable per group?
   
4. **Challenge Resolution**: If settlement is challenged, who decides?
   - Group vote (simple majority)?
   - Bet creator has final say?
   - Admin override?
   
5. **Badge Conflicts**: Can users have multiple badges or just one "primary"?
   - Multiple seems better for recognition
   - Primary could simplify display
   
6. **Point Inflation**: Over time, will points accumulate or naturally deflate?
   - Currently deflationary (losing bets remove points)
   - Should we add point generation mechanisms?

---

## Success Metrics

### Engagement Metrics
- Daily Active Users (DAU) per group
- Bets created per week per group
- Average participation rate (wagers per bet)
- Message frequency from bot
- User retention (week-over-week)

### Quality Metrics
- Average bet resolution time
- Challenge rate (% of bets challenged)
- Bailout utilization rate
- Badge distribution (is everyone getting something?)
- Season completion rate (users active start to end)

### Growth Metrics
- New groups added per week
- Group invitation rate
- Cross-platform adoption
- Event creation rate (if applicable)

---

## Risk Mitigation

### Technical Risks
- **LLM API Rate Limits**: Cache common responses, batch requests
- **Platform API Changes**: Abstract platform layer for easy swapping
- **Database Performance**: Index heavily queried fields, use read replicas if needed

### Product Risks
- **Low Engagement**: Focus on making bot messages genuinely entertaining
- **Toxic Behavior**: Moderate personalities, allow group admins to ban/kick
- **Complexity Creep**: Stay ruthlessly focused on core loop (create → wager → settle)

### Business Risks
- **LLM Costs**: Set reasonable token limits, cache aggressively
- **Platform Restrictions**: Ensure ToS compliance for each platform
- **Scaling**: Design for horizontal scaling from day one

---

## Future Considerations (Post-Launch)

- AI-generated bet suggestions based on group chat patterns
- Integration with external data sources (sports scores, weather, etc)
- Mobile app for richer bet creation experience
- Social sharing of funny bet outcomes
- Tournament mode (bracket-style betting)
- Bet templates/presets for common scenarios
- Analytics dashboard for group admins
- Premium features (custom badges, advanced personalities)