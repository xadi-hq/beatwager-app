<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Group;
use App\Models\User;

class GrudgeService
{
    /**
     * Get recent head-to-head history between two users
     *
     * @return array ['wins' => 3, 'losses' => 1, 'recent_events' => [...], 'narrative' => '...']
     */
    public function getRecentHistory(User $user1, User $user2, Group $group, int $limit = 5): array
    {
        // Get last N wagers between these two users
        $events = AuditEvent::where('group_id', $group->id)
            ->where('event_type', 'wager.won')
            ->where(function ($query) use ($user1, $user2) {
                // Both users must be participants (one as winner, one as loser)
                $query->whereJsonContains('participants', [['user_id' => $user1->id]])
                      ->whereJsonContains('participants', [['user_id' => $user2->id]]);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            return [
                'wins' => 0,
                'losses' => 0,
                'recent_events' => [],
                'narrative' => null,
            ];
        }

        // Calculate win/loss record
        $wins = 0;
        $losses = 0;
        $recentEvents = [];

        foreach ($events as $event) {
            $participants = $event->participants;
            $winner = collect($participants)->firstWhere('role', 'winner');

            if ($winner['user_id'] === $user1->id) {
                $wins++;
            } else {
                $losses++;
            }

            $recentEvents[] = [
                'date' => $event->created_at,
                'summary' => $event->summary,
                'winner_id' => $winner['user_id'],
            ];
        }

        // Generate narrative for LLM
        $narrative = $this->generateNarrative($user1, $user2, $wins, $losses, $recentEvents);

        return [
            'wins' => $wins,
            'losses' => $losses,
            'recent_events' => $recentEvents,
            'narrative' => $narrative,
        ];
    }

    /**
     * Generate human-readable narrative for LLM context
     */
    private function generateNarrative(User $user1, User $user2, int $wins, int $losses, array $events): ?string
    {
        if ($wins === 0 && $losses === 0) {
            return null;
        }

        $total = $wins + $losses;
        $narrative = "{$user1->name} has faced {$user2->name} {$total} time(s) recently. ";

        if ($wins > $losses) {
            $narrative .= "{$user1->name} is dominating with {$wins} win(s) vs {$losses} loss(es).";
        } elseif ($losses > $wins) {
            $narrative .= "{$user2->name} has the upper hand with {$user1->name} only winning {$wins} time(s).";
        } else {
            $narrative .= "They're evenly matched at {$wins}-{$losses}.";
        }

        // Add streak info
        if (count($events) >= 3) {
            $lastThree = array_slice($events, 0, 3);
            $allSameWinner = collect($lastThree)->pluck('winner_id')->unique()->count() === 1;

            if ($allSameWinner) {
                $streakWinner = $lastThree[0]['winner_id'] === $user1->id ? $user1->name : $user2->name;
                $narrative .= " {$streakWinner} is on a 3-wager winning streak!";
            }
        }

        return $narrative;
    }
}
