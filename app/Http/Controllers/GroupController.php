<?php

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

        return Inertia::render('Groups/Show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name ?? $group->platform_chat_title,
                'description' => $group->description,
                'currency' => $group->points_currency_name ?? 'points',
                'starting_balance' => $group->starting_balance,
                'decay_enabled' => $group->point_decay_enabled,
                'points_currency_name' => $group->points_currency_name ?? 'points',
                'group_type' => $group->group_type,
                'notification_preferences' => $notificationPreferences,
                'bot_tone' => $group->bot_tone,
                'llm_provider' => $group->llm_provider,
                'has_llm_configured' => !empty($group->llm_api_key),
                'llm_metrics' => $llmMetrics,
            ],
            'members' => $members,
            'stats' => $stats,
            'userBalance' => $userGroup->pivot->points,
        ]);
    }
}
