# Logging Architecture

## Overview

Three-tier logging approach:
1. **Audit Logs** - Detailed compliance/security trail (existing)
2. **Operational Logs** - Performance/monitoring/debugging (new)
3. **Audit Events** - Summarized events for LLM context (new)

---

## 1. Audit Logs (Existing)

**Purpose**: Compliance, security, detailed audit trail

**Use Cases**:
- Who did what, when, where
- Regulatory compliance
- Security investigations
- Detailed change tracking

**Current Schema**:
```sql
audit_logs:
- actor_id, actor_type
- action (wager.created, wager.settled)
- auditable_type, auditable_id
- metadata (json)
- ip_address, user_agent
- created_at
```

**Keep as-is** - Works well for compliance/security.

---

## 2. Operational Logs (New)

**Purpose**: Monitoring, debugging, performance analysis

**Use Cases**:
- LLM API call monitoring
- Performance metrics
- Error tracking
- Cost analysis
- Feature usage stats

**Approach**: Structured Laravel logging

### Implementation

```php
// config/logging.php
'channels' => [
    'operational' => [
        'driver' => 'daily',
        'path' => storage_path('logs/operational.log'),
        'level' => env('LOG_LEVEL', 'info'),
        'days' => 14,
        'formatter' => \Monolog\Formatter\JsonFormatter::class,
    ],
],
```

### Usage Patterns

```php
use Illuminate\Support\Facades\Log;

// LLM tracking
Log::channel('operational')->info('llm.generation', [
    'group_id' => $group->id,
    'provider' => $group->llm_provider,
    'message_key' => $ctx->key,
    'cached' => false,
    'duration_ms' => 245,
    'tokens_used' => 523,
    'cost_usd' => 0.00013,
    'fallback_used' => false,
]);

// Performance tracking
Log::channel('operational')->info('wager.settlement.performance', [
    'wager_id' => $wager->id,
    'participants' => $wager->bets_count,
    'duration_ms' => 1234,
    'queries' => 12,
]);

// Feature usage
Log::channel('operational')->info('feature.used', [
    'feature' => 'revenge_bet',
    'group_id' => $group->id,
    'user_id' => $user->id,
]);

// Errors (auto-captured by exception handler)
Log::channel('operational')->error('llm.api_error', [
    'provider' => 'anthropic',
    'error' => $exception->getMessage(),
    'group_id' => $group->id,
]);
```

### LogService Helper

```php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogService
{
    public static function llm(
        string $event,
        string $groupId,
        array $data = []
    ): void {
        Log::channel('operational')->info("llm.{$event}", [
            'group_id' => $groupId,
            'timestamp' => now()->toIso8601String(),
            ...$data,
        ]);
    }
    
    public static function performance(
        string $operation,
        int $durationMs,
        array $context = []
    ): void {
        Log::channel('operational')->info("performance.{$operation}", [
            'duration_ms' => $durationMs,
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ]);
    }
    
    public static function feature(
        string $feature,
        string $groupId,
        ?string $userId = null,
        array $metadata = []
    ): void {
        Log::channel('operational')->info("feature.{$feature}", [
            'group_id' => $groupId,
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
            ...$metadata,
        ]);
    }
}
```

### Analysis

```bash
# Recent LLM usage
grep "llm.generation" storage/logs/operational-*.log | jq -r '.context.group_id' | sort | uniq -c

# Cache hit rate
grep "llm.generation" storage/logs/operational-*.log | jq '.context.cached' | grep true | wc -l

# Average response time
grep "llm.generation" storage/logs/operational-*.log | jq '.context.duration_ms' | awk '{sum+=$1; count++} END {print sum/count}'

# Daily cost estimate
grep "llm.generation" storage/logs/operational-*.log | jq '.context.cost_usd' | awk '{sum+=$1} END {print sum}'
```

---

## 3. Audit Events (New)

**Purpose**: Summarized events for LLM context building

**Use Cases**:
- LLM needs recent group history
- "Sarah beat John 3 times this week"
- "Group just earned the Hot Streak badge"
- Building narrative context for messages

**Design Philosophy**:
- Human-readable summaries
- Pre-aggregated (not raw data)
- Fast to query
- Easy to understand

### Schema

```sql
audit_events:
- id (uuid)
- group_id (uuid, indexed)
- event_type (string, indexed: 'wager.won', 'badge.earned', 'streak.broken')
- summary (text: "Sarah won 'Marathon Bet' against John for 50 points")
- participants (json: [{user_id, username, role}, ...])
- impact (json: {points: 50, badge: 'streak_breaker', ...})
- metadata (json: flexible additional data)
- created_at (timestamp, indexed)
```

### Examples

```php
// Wager won
AuditEvent::create([
    'group_id' => $group->id,
    'event_type' => 'wager.won',
    'summary' => "Sarah won 'Will John finish the marathon?' against John for 50 points",
    'participants' => [
        ['user_id' => $sarah->id, 'username' => 'Sarah', 'role' => 'winner'],
        ['user_id' => $john->id, 'username' => 'John', 'role' => 'loser'],
    ],
    'impact' => ['points' => 50, 'streak' => 3],
    'metadata' => ['wager_id' => $wager->id],
]);

// Badge earned
AuditEvent::create([
    'group_id' => $group->id,
    'event_type' => 'badge.earned',
    'summary' => "Sarah earned 'Hot Streak' badge (5 wins in a row)",
    'participants' => [
        ['user_id' => $sarah->id, 'username' => 'Sarah', 'role' => 'earner'],
    ],
    'impact' => ['badge' => 'hot_streak', 'streak_count' => 5],
]);

// Grudge formed
AuditEvent::create([
    'group_id' => $group->id,
    'event_type' => 'grudge.intensified',
    'summary' => "Sarah now leads 5-2 against John in their rivalry",
    'participants' => [
        ['user_id' => $sarah->id, 'username' => 'Sarah', 'role' => 'leader'],
        ['user_id' => $john->id, 'username' => 'John', 'role' => 'underdog'],
    ],
    'impact' => ['leader_wins' => 5, 'underdog_wins' => 2],
]);
```

### AuditEvent Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditEvent extends Model
{
    use HasUuids;
    
    public $timestamps = false;
    
    protected $fillable = [
        'group_id',
        'event_type',
        'summary',
        'participants',
        'impact',
        'metadata',
        'created_at',
    ];
    
    protected function casts(): array
    {
        return [
            'participants' => 'array',
            'impact' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
    
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    
    // Get recent events for LLM context
    public static function recentForGroup(string $groupId, int $limit = 20)
    {
        return self::where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
    
    // Get events by type
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }
    
    // Get events involving user
    public function scopeInvolvingUser($query, string $userId)
    {
        return $query->whereJsonContains('participants', ['user_id' => $userId]);
    }
}
```

### EventService

```php
namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Group;
use App\Models\User;

class EventService
{
    public static function wagerWon(
        Group $group,
        User $winner,
        User $loser,
        int $points,
        string $wagerTitle,
        string $wagerId
    ): AuditEvent {
        return AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'wager.won',
            'summary' => "{$winner->username} won '{$wagerTitle}' against {$loser->username} for {$points} points",
            'participants' => [
                ['user_id' => $winner->id, 'username' => $winner->username, 'role' => 'winner'],
                ['user_id' => $loser->id, 'username' => $loser->username, 'role' => 'loser'],
            ],
            'impact' => ['points' => $points],
            'metadata' => ['wager_id' => $wagerId],
            'created_at' => now(),
        ]);
    }
    
    public static function badgeEarned(
        Group $group,
        User $user,
        string $badgeName,
        string $description,
        array $impact = []
    ): AuditEvent {
        return AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'badge.earned',
            'summary' => "{$user->username} earned '{$badgeName}' badge: {$description}",
            'participants' => [
                ['user_id' => $user->id, 'username' => $user->username, 'role' => 'earner'],
            ],
            'impact' => ['badge' => $badgeName, ...$impact],
            'created_at' => now(),
        ]);
    }
    
    public static function grudgeUpdate(
        Group $group,
        User $leader,
        User $underdog,
        int $leaderWins,
        int $underdogWins
    ): AuditEvent {
        return AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'grudge.updated',
            'summary' => "{$leader->username} now leads {$leaderWins}-{$underdogWins} against {$underdog->username}",
            'participants' => [
                ['user_id' => $leader->id, 'username' => $leader->username, 'role' => 'leader'],
                ['user_id' => $underdog->id, 'username' => $underdog->username, 'role' => 'underdog'],
            ],
            'impact' => ['leader_wins' => $leaderWins, 'underdog_wins' => $underdogWins],
            'created_at' => now(),
        ]);
    }
}
```

### Using in LLM Context

```php
// In LLMService or ContentGenerator
$recentEvents = AuditEvent::recentForGroup($group->id, 10);

$contextSummary = $recentEvents->map(fn($e) => $e->summary)->join("\n");

$prompt = "Recent group activity:\n{$contextSummary}\n\nNow announce this wager: {$wagerTitle}";
```

---

## Comparison

| Feature | Audit Logs | Operational Logs | Audit Events |
|---------|-----------|------------------|--------------|
| **Purpose** | Compliance | Monitoring | LLM Context |
| **Detail Level** | High | Medium | Low (summary) |
| **Retention** | Forever | 14 days | 90 days |
| **Query Speed** | Slower (detailed) | N/A (file-based) | Fast (indexed) |
| **Human Readable** | No (technical) | No (metrics) | Yes (narratives) |
| **Used By** | Admins, auditors | Devs, monitoring | LLM, features |
| **Storage** | Database | Files/logs | Database |

---

## Implementation Order

1. **Operational Logging** (immediate)
   - Add `operational` channel to `config/logging.php`
   - Create `LogService` helper
   - Add logging to `LLMService`
   - Add logging to `ContentGenerator`

2. **Audit Events** (next phase)
   - Create migration for `audit_events` table
   - Create `AuditEvent` model
   - Create `EventService` helper
   - Add event creation to wager settlement
   - Integrate into LLM context building

3. **Monitoring Dashboard** (future)
   - Parse operational logs
   - Aggregate metrics
   - Cost tracking
   - Performance dashboards

---

## Quick Start

### Operational Logging

```php
// In LLMService::generate()
$start = microtime(true);

try {
    $response = $this->callProvider(...);
    
    LogService::llm('generation.success', $group->id, [
        'provider' => $group->llm_provider,
        'cached' => false,
        'duration_ms' => (microtime(true) - $start) * 1000,
        'message_key' => $ctx->key,
    ]);
    
    return $response;
} catch (\Throwable $e) {
    LogService::llm('generation.failed', $group->id, [
        'provider' => $group->llm_provider,
        'error' => $e->getMessage(),
    ]);
    
    throw $e;
}
```

### Audit Events

```php
// In WagerSettlementService
$winner = $wager->getWinner();
$loser = $wager->getLoser();

EventService::wagerWon(
    group: $wager->group,
    winner: $winner,
    loser: $loser,
    points: $wager->stake,
    wagerTitle: $wager->title,
    wagerId: $wager->id
);
```

That's it!