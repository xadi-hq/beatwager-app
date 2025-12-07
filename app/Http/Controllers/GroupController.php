<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\LlmUsageDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    /**
     * Show group detail page with members
     */
    public function show(Group $group): Response
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        // Load group members with their stats
        $members = $group->users()->get()->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'telegram_username' => $user->getTelegramService()?->username,
            'balance' => $user->pivot->points,
            'points_earned' => $user->pivot->points_earned,
            'points_spent' => $user->pivot->points_spent,
            'event_attendance_streak' => $user->pivot->event_attendance_streak ?? 0,
            'role' => $user->pivot->role,
            'last_activity_at' => $user->pivot->last_activity_at?->toIso8601String(),
        ]);

        // Get group stats
        $stats = [
            'total_members' => $members->count(),
            'total_wagers' => $group->wagers()->count(),
            'active_wagers' => $group->wagers()->where('status', 'open')->count(),
            'total_events' => $group->events()->count(),
            'upcoming_events' => $group->events()->where('status', 'upcoming')->count(),
        ];

        // Get notification preferences with defaults
        $notificationPreferences = $group->notification_preferences ?? [
            'birthday_reminders' => false,
            'event_reminders' => true,
            'wager_reminders' => true,
            'weekly_summaries' => false,
        ];

        // Get LLM usage metrics for this month
        $llmMetrics = null;
        if (!empty($group->llm_api_key)) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $monthlyMetrics = LlmUsageDaily::where('group_id', $group->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get();

            if ($monthlyMetrics->isNotEmpty()) {
                $llmMetrics = [
                    'total_calls' => $monthlyMetrics->sum('total_calls'),
                    'cached_calls' => $monthlyMetrics->sum('cached_calls'),
                    'fallback_calls' => $monthlyMetrics->sum('fallback_calls'),
                    'estimated_cost_usd' => $monthlyMetrics->sum('estimated_cost_usd'),
                    'cache_hit_rate' => $monthlyMetrics->sum('total_calls') > 0
                        ? round(($monthlyMetrics->sum('cached_calls') / $monthlyMetrics->sum('total_calls')) * 100, 1)
                        : 0,
                ];
            }
        }

        // Get season data
        $currentSeason = null;
        if ($group->current_season_id) {
            $season = $group->currentSeason;
            if ($season) {
                $currentSeason = [
                    'id' => $season->id,
                    'season_number' => $season->season_number,
                    'started_at' => $season->started_at->toIso8601String(),
                    'is_active' => $season->is_active,
                    'days_elapsed' => max(1, (int) $season->started_at->diffInDays(now())),
                    'prize_structure' => $season->prize_structure,
                ];
            }
        }

        // Get past seasons (limited to last 10)
        $pastSeasons = $group->seasons()
            ->where('is_active', false)
            ->whereNotNull('ended_at')
            ->orderBy('season_number', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($season) => [
                'id' => $season->id,
                'season_number' => $season->season_number,
                'started_at' => $season->started_at->toIso8601String(),
                'ended_at' => $season->ended_at?->toIso8601String(),
                'duration_days' => $season->getDurationInDays(),
                'winner' => $season->getWinner(),
                'total_participants' => $season->stats['total_participants'] ?? 0,
                'total_wagers' => $season->stats['total_wagers'] ?? 0,
            ]);

        return Inertia::render('Groups/Show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name ?? $group->platform_chat_title,
                'description' => $group->description,
                'currency' => $group->points_currency_name ?? 'points',
                'starting_balance' => $group->starting_balance,
                'timezone' => $group->timezone ?? 'UTC',
                'language' => $group->language ?? 'en',
                'decay_enabled' => $group->point_decay_enabled,
                'points_currency_name' => $group->points_currency_name ?? 'points',
                'group_type' => $group->group_type,
                'notification_preferences' => $notificationPreferences,
                'bot_tone' => $group->bot_tone,
                'llm_provider' => $group->llm_provider,
                'allow_nsfw' => $group->allow_nsfw ?? false,
                'has_llm_configured' => !empty($group->llm_api_key),
                'llm_metrics' => $llmMetrics,
                'current_season' => $currentSeason,
                'season_ends_at' => $group->season_ends_at?->toIso8601String(),
                'superchallenge_frequency' => $group->superchallenge_frequency ?? 'off',
            ],
            'members' => $members,
            'stats' => $stats,
            'userBalance' => $userGroup->pivot->points,
            'pastSeasons' => $pastSeasons,
        ]);
    }
}
