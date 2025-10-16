<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Group;
use App\Models\User;

/**
 * Service for creating audit events - human-readable summaries for LLM context
 * 
 * These are different from audit logs (compliance) - they're narrative summaries
 */
class AuditEventService
{
    /**
     * Log a wager win event
     */
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

    /**
     * Log a badge earned event
     */
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

    /**
     * Log a grudge/rivalry update
     */
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

    /**
     * Log a streak achievement
     */
    public static function streakAchieved(
        Group $group,
        User $user,
        int $streakCount,
        string $streakType = 'win'
    ): AuditEvent {
        return AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'streak.achieved',
            'summary' => "{$user->username} achieved a {$streakCount} {$streakType} streak",
            'participants' => [
                ['user_id' => $user->id, 'username' => $user->username, 'role' => 'achiever'],
            ],
            'impact' => ['streak_count' => $streakCount, 'streak_type' => $streakType],
            'created_at' => now(),
        ]);
    }

    /**
     * Log a streak broken event
     */
    public static function streakBroken(
        Group $group,
        User $victim,
        User $breaker,
        int $streakCount
    ): AuditEvent {
        return AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'streak.broken',
            'summary' => "{$breaker->username} broke {$victim->username}'s {$streakCount} win streak",
            'participants' => [
                ['user_id' => $victim->id, 'username' => $victim->username, 'role' => 'victim'],
                ['user_id' => $breaker->id, 'username' => $breaker->username, 'role' => 'breaker'],
            ],
            'impact' => ['streak_count' => $streakCount],
            'created_at' => now(),
        ]);
    }

    /**
     * Generic event creator for custom events
     */
    public static function create(
        Group $group,
        string $eventType,
        string $summary,
        array $participants = [],
        array $impact = [],
        array $metadata = []
    ): AuditEvent {
        return AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => $eventType,
            'summary' => $summary,
            'participants' => $participants,
            'impact' => $impact,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
