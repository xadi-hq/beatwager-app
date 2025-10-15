# Architecture Decisions Document

**Project:** Social Wagering Platform  
**Version:** 1.0  
**Date:** October 13, 2025  
**Status:** Approved

---

## Executive Summary

This document outlines the technical architecture decisions for the Social Wagering Platform. After evaluating multiple technology stacks, we've selected **Laravel + Inertia.js + Vue 3** as the foundation, prioritizing rapid development, maintainability, and team familiarity.

**Key Decision:** Platform-agnostic architecture with thin messaging layer, comprehensive web interface, and robust backend services.

---

## Technology Stack

### Backend: Laravel 12.x (PHP 8.4)

**Why Laravel:**
1. **Batteries-included framework** - Queues, jobs, events, scheduler, migrations all built-in
2. **Rapid development** - We need Phase 1 in 8-10 weeks
3. **Excellent for scheduled tasks** - Critical for our decay, bonuses, reminders
4. **Team familiarity** - Developers already comfortable with Laravel
5. **Mature ecosystem** - Telegram bot SDKs, OAuth libraries well-documented
6. **Service layer architecture** - Natural fit for our WagerService, PointService pattern

**Core Laravel Features We'll Use:**
- **Eloquent ORM** - Clean models, relationships, query builder
- **Migrations** - Schema will evolve heavily through phases
- **Queue System** - Essential for Telegram rate limits and notifications
- **Task Scheduler** - Multiple daily/weekly scheduled jobs
- **Events & Listeners** - Loose coupling for notifications and side effects
- **Validation** - Request validation with automatic error responses
- **Service Container** - Dependency injection for testability

### Frontend: Vue 3 (Composition API) + Inertia.js v2

**Why This Combo:**
1. **Inertia.js bridges SPA + server-rendered** - Best of both worlds
2. **No API versioning** - Server-side routing, forms post directly to Laravel
3. **Shared validation** - Backend validates, frontend shows errors inline
4. **SPA-like UX** - No page refreshes, smooth transitions
5. **SEO-friendly** - Server-rendered on first load
6. **Perfect for our use case** - Mostly traditional forms, not heavy real-time

**Vue 3 Setup:**
```javascript
// Composition API with <script setup>
<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  question: '',
  stake: 100,
  deadline: null
})

const submit = () => {
  form.post('/wagers/create')
}
</script>
```

**TypeScript Gradual Adoption:**
- Start with JavaScript for speed
- Add TypeScript to critical components (forms, composables)
- Full TypeScript migration in Phase 2 if beneficial

### Styling: Tailwind CSS 4.x

**Why Tailwind:**
- Utility-first approach matches Vue component style
- Rapid prototyping
- Consistent design system
- Small production bundle (PurgeCSS)
- Team already familiar

### Database: PostgreSQL 17

**Why PostgreSQL over MySQL:**
1. **Better JSON support** - We use JSONB for wager options, custom fields
2. **ACID compliance** - Critical for point transactions
3. **Superior handling of concurrent writes** - Multiple users joining wagers simultaneously
4. **Window functions** - Useful for leaderboards, rankings
5. **Full-text search** - Future feature potential

**Schema Approach:**
- UUIDs for primary keys (better for distributed systems, if needed later)
- Timestamps on all tables (audit trail)
- Proper foreign key constraints (data integrity)
- Strategic indexes (query performance)

### Cache & Queue: Redis 8

**Why Redis:**
1. **Laravel's default cache driver** - Zero configuration
2. **Queue backend** - Fast, reliable
3. **Session storage** - Fast user lookups
4. **Future pub/sub** - If we add real-time features (Phase 3)

**Redis Usage:**
```
Cache keys:
- user:{user_id}:balance:{group_id}
- group:{group_id}:leaderboard
- wager:{wager_id}:participants

Queue names:
- default (general jobs)
- telegram (rate-limited notifications)
- high (urgent tasks like settlement)
```

---

## Architecture Patterns

### Platform-Agnostic Design

**Critical Principle:** Messaging platforms (Telegram, Slack, Discord) are **thin interfaces only**.

```
┌─────────────────────────────────────────────┐
│           MESSAGING LAYER (Thin)            │
│  Telegram │ Slack │ Discord │ Future...     │
│  - Receive commands                          │
│  - Send formatted messages                   │
│  - Parse simple inputs                       │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────┐
│          WEB INTERFACE (Primary)            │
│  - Wager creation forms                     │
│  - Settlement interfaces                    │
│  - Attendance recording                     │
│  - Dispute voting                           │
│  - Dashboard & analytics                    │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────┐
│         BACKEND SERVICES (Logic)            │
│  WagerService │ PointService │ EventService │
│  - All business rules                       │
│  - Validation                               │
│  - Calculations                             │
│  - Transaction management                   │
└─────────────────────────────────────────────┘
```

### Service Layer Pattern

**All business logic lives in services, not controllers.**

```php
// Bad ❌
class WagerController {
    public function settle(Request $request, Wager $wager) {
        // 100 lines of business logic in controller
    }
}

// Good ✅
class WagerController {
    public function settle(
        Request $request, 
        Wager $wager,
        WagerService $wagerService
    ) {
        $outcome = $request->validated('outcome');
        $wagerService->settleWager($wager, auth()->user(), $outcome);
        
        return redirect()->route('wagers.show', $wager);
    }
}
```

**Service Structure:**
```
App/Services/
├── WagerService.php
│   ├── createWager(array $data, User $creator, Group $group): Wager
│   ├── joinWager(Wager $wager, User $user, array $answer): WagerEntry
│   ├── settleWager(Wager $wager, User $settler, mixed $outcome): void
│   ├── calculateWinnings(Wager $wager): Collection
│   └── canUserJoin(Wager $wager, User $user): bool
│
├── PointService.php
│   ├── getUserBalance(User $user, Group $group): int
│   ├── reservePoints(User $user, Group $group, int $amount): Transaction
│   ├── distributeWinnings(Wager $wager, Collection $winners): void
│   ├── refundStakes(Wager $wager): void
│   ├── grantWeeklyBonus(User $user, Group $group): void
│   └── applyDecay(User $user, Group $group): void
│
├── TokenService.php
│   ├── generateToken(User $user, string $action, array $context): string
│   ├── validateToken(string $token): OneTimeToken
│   └── consumeToken(OneTimeToken $token): void
│
├── SeasonService.php
│   ├── createSeason(Group $group, array $data): Season
│   ├── startSeason(Season $season): void
│   ├── endSeason(Season $season): void
│   ├── getLeaderboard(Season $season): Collection
│   └── calculateStandings(Season $season): array
│
├── EventService.php
│   ├── createEvent(Group $group, array $data): Event
│   ├── recordAttendance(Event $event, User $reporter, array $attendees): void
│   ├── challengeAttendance(Event $event, User $challenger, User $disputed, string $reason): Challenge
│   ├── voteOnChallenge(Challenge $challenge, User $voter, bool $vote): void
│   ├── resolveChallenge(Challenge $challenge): void
│   └── awardEventBonuses(Event $event): void
│
├── DisputeService.php
│   ├── createDispute(Wager $wager, User $user, string $reason): Dispute
│   ├── triggerVote(Dispute $dispute): void
│   ├── castVote(Dispute $dispute, User $user, string $vote): void
│   └── resolveDispute(Dispute $dispute): void
│
└── Messaging/
    ├── MessengerInterface.php
    ├── TelegramMessenger.php
    ├── SlackMessenger.php (Phase 3)
    └── MessengerFactory.php
```

### Messenger Interface Pattern

**All messaging platforms implement this interface:**

```php
interface MessengerInterface
{
    // Outbound messages
    public function sendMessage(string $chatId, string $text, ?array $buttons = null): void;
    public function sendPrivateMessage(string $userId, string $text): void;
    public function updateMessage(string $messageId, string $text, ?array $buttons = null): void;
    
    // Message formatting
    public function formatWagerAnnouncement(Wager $wager): array;
    public function formatSettlementNotification(Wager $wager, Collection $winners): array;
    public function formatEventAnnouncement(Event $event): array;
    
    // Platform identification
    public function getPlatformName(): string;
    public function getPlatformIcon(): string;
}
```

**Implementation Example:**

```php
class TelegramMessenger implements MessengerInterface
{
    public function sendMessage(string $chatId, string $text, ?array $buttons = null): void
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        
        if ($buttons) {
            $payload['reply_markup'] = [
                'inline_keyboard' => $this->formatButtons($buttons),
            ];
        }
        
        $this->telegramApi->sendMessage($payload);
    }
    
    public function formatWagerAnnouncement(Wager $wager): array
    {
        $text = "🎲 *New Wager from @{$wager->creator->username}*\n\n";
        $text .= "{$wager->question}\n\n";
        $text .= "💰 Stake: {$wager->stake} points\n";
        $text .= "⏰ Closes: {$wager->deadline->format('M d, g:i A')}\n";
        $text .= "📊 Type: {$this->formatWagerType($wager->type)}\n\n";
        $text .= "Participants ({$wager->entries_count}):";
        
        $buttons = $this->buildJoinButtons($wager);
        
        return ['text' => $text, 'buttons' => $buttons];
    }
    
    private function buildJoinButtons(Wager $wager): array
    {
        return match($wager->type) {
            'binary' => [
                [
                    ['text' => "✅ Yes - {$wager->stake}pts", 'callback_data' => "join:{$wager->id}:yes"],
                    ['text' => "❌ No - {$wager->stake}pts", 'callback_data' => "join:{$wager->id}:no"],
                ]
            ],
            'multiple_choice' => $this->buildMultipleChoiceButtons($wager),
            'numeric' => [
                [['text' => "📝 Reply with your guess", 'callback_data' => "hint:numeric:{$wager->id}"]]
            ],
        };
    }
}
```

### Event-Driven Architecture

**Use Laravel events for loose coupling:**

```php
// When a wager is created
event(new WagerCreated($wager));

// Listeners handle side effects
class SendWagerAnnouncement
{
    public function handle(WagerCreated $event): void
    {
        $messenger = app(MessengerFactory::class)->make($event->wager->group->platform);
        $messenger->sendMessage(
            $event->wager->group->platform_group_id,
            ...
        );
    }
}
```

**Key Events:**
- `WagerCreated` → SendWagerAnnouncement
- `WagerJoined` → UpdateWagerMessage, NotifyCreator
- `WagerSettled` → DistributePoints, NotifyParticipants
- `SeasonEnded` → FreezeStandings, NotifyGroup
- `EventAttendanceRecorded` → AwardBonuses, NotifyAttendees
- `DisputeCreated` → NotifyParticipants, InitiateVoting

---

## Docker Architecture

### Container Strategy

**5 containers:**
1. **app** - Laravel application (web + Inertia SSR)
2. **postgres** - PostgreSQL database
3. **redis** - Cache + queue backend
4. **queue** - Laravel queue worker (processes jobs)
5. **scheduler** - Laravel scheduler (cron jobs)

### Docker Compose Configuration

```yaml
version: '3.8'

services:
  # Laravel application
  app:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        - PHP_VERSION=8.3
    container_name: wager-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    ports:
      - "${APP_PORT:-8000}:8000"
    environment:
      - APP_ENV=${APP_ENV:-local}
      - APP_DEBUG=${APP_DEBUG:-true}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - QUEUE_CONNECTION=redis
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - wager-network
    healthcheck:
      test: ["CMD", "php", "artisan", "health:check"]
      interval: 30s
      timeout: 10s
      retries: 3

  # PostgreSQL database
  postgres:
    image: postgres:16-alpine
    container_name: wager-postgres
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      PGDATA: /var/lib/postgresql/data/pgdata
    volumes:
      - postgres-data:/var/lib/postgresql/data
    ports:
      - "${DB_PORT:-5432}:5432"
    networks:
      - wager-network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME}"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis for cache and queues
  redis:
    image: redis:7-alpine
    container_name: wager-redis
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD:-null}
    volumes:
      - redis-data:/data
    ports:
      - "${REDIS_PORT:-6379}:6379"
    networks:
      - wager-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Laravel queue worker
  queue:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        - PHP_VERSION=8.3
    container_name: wager-queue
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    command: php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=90
    environment:
      - APP_ENV=${APP_ENV:-local}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - QUEUE_CONNECTION=redis
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - wager-network

  # Laravel scheduler
  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        - PHP_VERSION=8.3
    container_name: wager-scheduler
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    command: php artisan schedule:work
    environment:
      - APP_ENV=${APP_ENV:-local}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - wager-network

volumes:
  postgres-data:
    driver: local
  redis-data:
    driver: local

networks:
  wager-network:
    driver: bridge
```

### Dockerfile

```dockerfile
ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    nodejs \
    npm \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --no-progress --prefer-dist

# Install Node dependencies and build frontend
RUN npm ci && npm run build

# Set proper permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php artisan health:check || exit 1

# Start PHP-FPM server
CMD php artisan serve --host=0.0.0.0 --port=8000
```

### Development vs Production

**Development (docker-compose.yml):**
- Hot reload enabled
- Volume mounts for live code updates
- Debug mode on
- Local .env file

**Production (docker-compose.prod.yml):**
- No volume mounts (baked into image)
- Optimized builds (no dev dependencies)
- Debug mode off
- Secrets from environment/vault
- Health checks more aggressive
- Resource limits defined

---

## Scheduled Tasks Architecture

### Laravel Scheduler Configuration

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Point decay - daily at 1 AM
    $schedule->job(new ApplyPointDecay)
        ->dailyAt('01:00')
        ->withoutOverlapping()
        ->onOneServer();
    
    // Cleanup expired tokens - daily at 2 AM
    $schedule->job(new CleanupExpiredTokens)
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->onOneServer();
    
    // Deadline reminders - hourly
    $schedule->job(new SendDeadlineReminders)
        ->hourly()
        ->withoutOverlapping()
        ->onOneServer();
    
    // Event attendance prompts - hourly
    $schedule->job(new SendEventAttendancePrompts)
        ->hourly()
        ->withoutOverlapping()
        ->onOneServer();
    
    // Lock event attendance after 24hr challenge window - hourly
    $schedule->job(new LockEventAttendance)
        ->hourly()
        ->withoutOverlapping()
        ->onOneServer();
    
    // Weekly bonuses - Sunday at 11:59 PM
    $schedule->job(new DistributeWeeklyBonuses)
        ->weekly()
        ->sundays()
        ->at('23:59')
        ->withoutOverlapping()
        ->onOneServer();
    
    // Health checks - every 5 minutes
    $schedule->command('health:check')
        ->everyFiveMinutes();
}
```

**Key Flags:**
- `withoutOverlapping()` - Prevents job from running if previous instance still running
- `onOneServer()` - In multi-server setup, only runs on one server
- `->when()` - Conditional execution (e.g., only run if feature flag enabled)

### Job Structure Example

```php
class ApplyPointDecay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PointService $pointService): void
    {
        // Get all users inactive for 14+ days
        UserGroup::query()
            ->where('last_wager_joined_at', '<', now()->subDays(14))
            ->whereNotNull('last_wager_joined_at')
            ->chunk(100, function ($userGroups) use ($pointService) {
                foreach ($userGroups as $userGroup) {
                    try {
                        $pointService->applyDecay(
                            $userGroup->user,
                            $userGroup->group
                        );
                    } catch (\Exception $e) {
                        Log::error('Decay application failed', [
                            'user_id' => $userGroup->user_id,
                            'group_id' => $userGroup->group_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
```

---

## Database Schema Decisions

### UUID vs Auto-increment IDs

**Decision: Use UUIDs for primary keys**

**Rationale:**
- Better for distributed systems (if we scale horizontally later)
- No ID enumeration attacks (security)
- Can generate IDs client-side if needed
- Better for merging data from different sources

**Trade-off:**
- Slightly larger storage (16 bytes vs 4-8 bytes)
- Slightly slower joins (negligible at our scale)

**Implementation:**
```php
// Migration
Schema::create('wagers', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('group_id');
    $table->uuid('creator_id');
    // ...
    $table->timestamps();
    
    $table->foreign('group_id')->references('id')->on('groups');
    $table->foreign('creator_id')->references('id')->on('users');
});

// Model
class Wager extends Model
{
    use HasUuids;
    
    protected $keyType = 'string';
    public $incrementing = false;
}
```

### Index Strategy

**Critical indexes for performance:**

```sql
-- Wagers
CREATE INDEX idx_wagers_group_status ON wagers(group_id, status);
CREATE INDEX idx_wagers_deadline ON wagers(deadline) WHERE status IN ('open', 'closed');
CREATE INDEX idx_wagers_creator ON wagers(creator_id);

-- Wager Entries
CREATE INDEX idx_entries_wager ON wager_entries(wager_id);
CREATE INDEX idx_entries_user ON wager_entries(user_id);
CREATE UNIQUE INDEX idx_entries_unique ON wager_entries(wager_id, user_id);

-- Transactions (audit trail)
CREATE INDEX idx_transactions_user_group ON transactions(user_id, group_id, created_at DESC);
CREATE INDEX idx_transactions_reference ON transactions(reference_id, type);

-- User Groups (for balance lookups)
CREATE UNIQUE INDEX idx_user_group_unique ON user_group(user_id, group_id);
CREATE INDEX idx_user_group_decay ON user_group(last_wager_joined_at) WHERE last_wager_joined_at IS NOT NULL;

-- Events
CREATE INDEX idx_events_group_status ON group_events(group_id, status);
CREATE INDEX idx_events_date ON group_events(event_date);
```

### Transaction Isolation

**For point operations, use serializable transactions:**

```php
DB::transaction(function () use ($wager, $user, $stake) {
    // Lock user's balance row
    $userGroup = UserGroup::where('user_id', $user->id)
        ->where('group_id', $wager->group_id)
        ->lockForUpdate()
        ->first();
    
    if ($userGroup->current_points < $stake) {
        throw new InsufficientPointsException;
    }
    
    // Reserve points
    $userGroup->decrement('current_points', $stake);
    
    // Create entry
    WagerEntry::create([...]);
    
    // Log transaction
    Transaction::create([...]);
}, 3); // Retry 3 times on deadlock
```

---

## Security Considerations

### Telegram Webhook Validation

**Always verify requests are from Telegram:**

```php
class VerifyTelegramWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $token = config('services.telegram.bot_token');
        $secretToken = config('services.telegram.webhook_secret');
        
        // Verify secret token header
        if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== $secretToken) {
            abort(403, 'Invalid webhook token');
        }
        
        return $next($request);
    }
}
```

### One-Time Token Security

**Tokens must be:**
- Cryptographically random (not sequential)
- Single-use only
- Time-limited (expire after 24 hours)
- Tied to specific user and action

```php
class TokenService
{
    public function generateToken(User $user, string $action, array $context): string
    {
        $token = Str::random(64);
        
        OneTimeToken::create([
            'token' => hash('sha256', $token), // Hash before storing
            'user_id' => $user->id,
            'action' => $action,
            'context' => $context,
            'expires_at' => now()->addHours(24),
        ]);
        
        return $token; // Return unhashed for URL
    }
    
    public function validateToken(string $token): OneTimeToken
    {
        $hashed = hash('sha256', $token);
        
        $tokenModel = OneTimeToken::where('token', $hashed)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->firstOrFail();
        
        return $tokenModel;
    }
}
```

### Rate Limiting

**Protect against abuse:**

```php
// routes/web.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/wagers/create', [WagerController::class, 'store']);
    Route::post('/wagers/{wager}/settle', [WagerController::class, 'settle']);
});

// For Telegram webhook - more generous
Route::post('/api/telegram/webhook', [TelegramController::class, 'handle'])
    ->middleware('throttle:100,1');
```

### CSRF Protection

**Inertia handles this automatically, but be aware:**
- All POST/PUT/DELETE requests include CSRF token
- Telegram webhook route must be excluded from CSRF middleware
- One-time tokens provide additional protection for sensitive actions

---

## Performance Optimization Strategy

### Caching Strategy

**What to cache:**
```php
// User balance (cache for 5 minutes)
Cache::remember(
    "user:{$userId}:balance:{$groupId}",
    300,
    fn() => $pointService->getUserBalance($user, $group)
);

// Leaderboard (cache for 1 hour, invalidate on point changes)
Cache::remember(
    "group:{$groupId}:leaderboard",
    3600,
    fn() => $seasonService->getLeaderboard($season)
);

// Active wagers for group (cache for 1 minute)
Cache::remember(
    "group:{$groupId}:active_wagers",
    60,
    fn() => Wager::where('group_id', $groupId)
        ->where('status', 'open')
        ->with('creator', 'entries')
        ->get()
);
```

**Cache Invalidation:**
```php
// When points change
event(new PointsChanged($user, $group));

// Listener
class InvalidateBalanceCache
{
    public function handle(PointsChanged $event): void
    {
        Cache::forget("user:{$event->user->id}:balance:{$event->group->id}");
        Cache::forget("group:{$event->group->id}:leaderboard");
    }
}
```

### Query Optimization

**Always eager load relationships:**
```php
// Bad ❌ (N+1 query)
$wagers = Wager::where('group_id', $groupId)->get();
foreach ($wagers as $wager) {
    echo $wager->creator->name; // Query per wager
}

// Good ✅
$wagers = Wager::where('group_id', $groupId)
    ->with('creator', 'entries.user')
    ->get();
```

**Use database aggregates:**
```php
// Bad ❌ (loads all entries into memory)
$participantCount = $wager->entries->count();

// Good ✅
$participantCount = $wager->entries()->count();
```

### Queue Strategy

**Queue priorities:**
```php
// High priority (settlement, critical notifications)
SendSettlementNotification::dispatch($wager)
    ->onQueue('high');

// Normal priority (general notifications)
SendWagerAnnouncement::dispatch($wager)
    ->onQueue('default');

// Low priority (weekly bonuses, non-urgent)
DistributeWeeklyBonuses::dispatch()
    ->onQueue('low');
```

**Queue workers:**
```bash
# Production setup (Supervisor)
[program:wager-queue-high]
command=php artisan queue:work redis --queue=high --tries=3 --timeout=90
numprocs=2

[program:wager-queue-default]
command=php artisan queue:work redis --queue=default --tries=3 --timeout=90
numprocs=3

[program:wager-queue-low]
command=php artisan queue:work redis --queue=low --tries=3 --timeout=90
numprocs=1
```

---

## Monitoring & Observability

### Logging Strategy

**Log Channels:**
```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],
    
    'telegram' => [
        'driver' => 'daily',
        'path' => storage_path('logs/telegram.log'),
        'level' => 'debug',
    ],
    
    'points' => [
        'driver' => 'daily',
        'path' => storage_path('logs/points.log'),
        'level' => 'info',
    ],
];
```

**What to log:**
```php
// All point transactions
Log::channel('points')->info('Points distributed', [
    'wager_id' => $wager->id,
    'winners' => $winners->pluck('id'),
    'amounts' => $amounts,
]);

// Telegram API failures
Log::channel('telegram')->error('Failed to send message', [
    'chat_id' => $chatId,
    'error' => $exception->getMessage(),
]);

// Suspicious activity
Log::warning('Multiple failed join attempts', [
    'user_id' => $user->id,
    'wager_id' => $wager->id,
    'attempts' => $attempts,
]);
```

### Health Checks

```php
// app/Console/Commands/HealthCheck.php
public function handle(): int
{
    $checks = [
        'database' => $this->checkDatabase(),
        'redis' => $this->checkRedis(),
        'queue' => $this->checkQueue(),
        'storage' => $this->checkStorage(),
    ];
    
    foreach ($checks as $service => $healthy) {
        if (!$healthy) {
            $this->error("❌ {$service} is unhealthy");
            return 1;
        }
    }
    
    $this->info("✅ All systems operational");
    return 0;
}
```

### Metrics to Track

**Application Metrics:**
- Wagers created per day
- Average time to settlement
- Dispute rate
- Point distribution (histogram)
- User retention (cohort analysis)

**Technical Metrics:**
- API response times (p50, p95, p99)
- Queue job processing time
- Redis memory usage
- Database connection pool usage
- Failed job rate

**Use Laravel Telescope in development for visibility:**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

---

## Testing Strategy

### Test Pyramid

```
           ┌───────────────┐
           │   E2E Tests   │  (10%)
           │  (Selenium)   │
           └───────────────┘
        ┌───────────────────────┐
        │  Integration Tests    │  (30%)
        │  (Feature Tests)      │
        └───────────────────────┘
    ┌───────────────────────────────┐
    │      Unit Tests               │  (60%)
    │   (Service Layer Tests)       │
    └───────────────────────────────┘
```

**Unit Tests (Service Layer):**
```php
class WagerServiceTest extends TestCase
{
    public function test_user_cannot_join_wager_with_insufficient_points()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        UserGroup::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'current_points' => 50, // Not enough
        ]);
        
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'stake' => 100,
        ]);
        
        $this->expectException(InsufficientPointsException::class);
        
        app(WagerService::class)->joinWager($wager, $user, ['position' => 'yes']);
    }
}
```

**Integration Tests (Full Flow):**
```php
class WagerCreationTest extends TestCase
{
    public function test_complete_wager_lifecycle()
    {
        // Setup
        $creator = User::factory()->create();
        $this->actingAs($creator);
        
        // Create wager
        $response = $this->post('/wagers/create', [
            'question' => 'Will it rain tomorrow?',
            'type' => 'binary',
            'stake' => 100,
            'deadline' => now()->addDay(),
        ]);
        
        $wager = Wager::first();
        $this->assertNotNull($wager);
        
        // Another user joins
        $participant = User::factory()->create();
        $this->actingAs($participant);
        
        $response = $this->post("/wagers/{$wager->id}/join", [
            'position' => 'yes',
        ]);
        
        $this->assertDatabaseHas('wager_entries', [
            'wager_id' => $wager->id,
            'user_id' => $participant->id,
        ]);
        
        // Creator settles
        $this->actingAs($creator);
        $this->post("/wagers/{$wager->id}/settle", [
            'outcome' => 'yes',
        ]);
        
        $this->assertEquals('settled', $wager->fresh()->status);
    }
}
```

---

## Deployment Strategy

### Environments

1. **Local (Docker)** - Development
2. **Staging** - Pre-production testing
3. **Production** - Live system

### CI/CD Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main, staging]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run tests
        run: |
          composer install
          php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Deploy to production
        run: |
          # SSH into server
          # Pull latest code
          # Run migrations
          # Restart services
```

### Zero-Downtime Deployments

**Strategy:**
1. Deploy new code
2. Run migrations (must be backwards compatible)
3. Restart queue workers gracefully
4. Restart PHP-FPM
5. Clear caches

```bash
#!/bin/bash
# deploy.sh

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Restart queue workers (graceful stop)
php artisan queue:restart

# Clear and rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reload PHP-FPM
sudo systemctl reload php8.3-fpm

echo "✅ Deployment complete"
```

---

## Future Considerations

### Scaling Horizontal (Phase 3+)

**When to consider:**
- >1000 concurrent users
- >100 active groups
- Queue processing time >1 minute

**How to scale:**
1. **Multiple app servers behind load balancer**
   - Sticky sessions (or use Redis for sessions)
   - Shared storage for Laravel storage (S3)
   
2. **Dedicated queue workers**
   - Separate servers just for queue processing
   - Scale workers independently
   
3. **Database read replicas**
   - Write to primary
   - Read from replicas
   - Laravel handles this with `DB::table()->readPdo()`

### Real-time Features (Phase 3)

**If we add live updates:**
- Use Laravel Reverb (built-in WebSocket server)
- Or external service like Pusher, Ably
- Publish events from Laravel to WebSocket server

**Example:**
```php
// In Laravel
broadcast(new WagerJoined($wager));

// In Vue
Echo.channel(`wager.${wagerId}`)
    .listen('WagerJoined', (e) => {
        // Update participant count live
    });
```

### Multi-tenancy (If Scaling to Many Organizations)

**Not needed for Phase 1-2, but if building SaaS later:**
- Use `spatie/laravel-multitenancy` package
- Separate database per organization (better isolation)
- OR tenant_id column on all tables (simpler, less isolation)

---

## Decision Log

### Why NOT Node.js/Fastify?

**Considered:** Fastify + Better-Auth + Vue SPA

**Rejected Because:**
- More setup overhead (job queues, cron, migrations, ORM)
- Scheduled tasks require additional orchestration
- Team less familiar (slower development)
- SPA adds complexity (CORS, API versioning, separate deployments)
- Node's async nature less critical for our use case (mostly sync operations)

**Re-evaluate if:**
- Project becomes heavily real-time
- We need ultra-high request throughput (>10k req/s)
- Team becomes primarily JavaScript/TypeScript developers

### Why Inertia over API + SPA?

**Considered:** Laravel API backend + Vue SPA frontend

**Chose Inertia Because:**
- Simpler authentication (no JWT/tokens)
- No API versioning concerns
- Shared validation (one source of truth)
- Faster development (fewer files, less boilerplate)
- Better SEO out of the box
- Perfect for form-heavy applications like ours

**Trade-offs:**
- Can't easily build native mobile apps later (would need API anyway)
- Less suitable for heavily real-time features (but we don't have many)

**Mitigation:** If we need mobile apps in Phase 3, we can add Laravel Sanctum API alongside Inertia.

### Why PostgreSQL over MySQL?

**Chose PostgreSQL Because:**
- Better JSON support (JSONB with indexes)
- Window functions (useful for leaderboards)
- ACID compliance
- Better concurrent write handling
- More advanced features we might need later

**Trade-offs:**
- Slightly less ubiquitous than MySQL
- Marginally more resource intensive

### Why Redis over Database for Queues?

**Chose Redis Because:**
- Much faster than database queues
- Supports job priorities easily
- Atomic operations (critical for rate limiting)
- Also serves as cache, sessions (one service, multiple uses)

**Trade-offs:**
- One more service to manage
- Jobs lost if Redis crashes (but we don't need guaranteed delivery for notifications)

---

## Conclusion

This architecture is designed for:
✅ **Rapid development** - Ship Phase 1 in 8-10 weeks  
✅ **Maintainability** - Clear separation of concerns  
✅ **Scalability** - Can handle 100s of groups easily, thousands with minor changes  
✅ **Extensibility** - Easy to add new messenger platforms  
✅ **Team familiarity** - Leverage existing Laravel/Vue knowledge  

**Next Steps:**
1. Set up Docker development environment
2. Initialize Laravel project
3. Configure Inertia + Vue
4. Create database schema migrations
5. Start building service layer

Let's build this! 🚀