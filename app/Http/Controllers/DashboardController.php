<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OneTimeToken;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard
     *
     * Authentication handled by 'signed.auth' middleware - user is already authenticated via session
     */
    public function show(Request $request): Response
    {
        // Get authenticated user from session (middleware handles auth)
        $user = Auth::user();

        // Optional: Get focus and group from query parameters
        $focus = $request->query('focus', 'wagers');
        $groupId = $request->query('group_id');

        // Get user's groups with balances
        $groups = $user->groups()->get()->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'balance' => $g->pivot->points,
            'role' => $g->pivot->role,
        ]);

        // Get user's active wagers (created or joined) - split by deadline
        $activeWagersQuery = Wager::where('status', 'open')
            ->where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhereHas('entries', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['group', 'entries.user'])
            ->orderBy('deadline', 'asc')
            ->get();

        $now = now();
        $openWagers = collect();
        $awaitingSettlement = collect();

        foreach ($activeWagersQuery as $wager) {
            $userEntry = $wager->entries->firstWhere('user_id', $user->id);

            $wagerData = [
                'id' => $wager->id,
                'title' => $wager->title,
                'url' => route('wager.show', ['wager' => $wager->id]),
                'group' => ['id' => $wager->group->id, 'name' => $wager->group->name],
                'type' => $wager->type,
                'stake_amount' => $wager->stake_amount,
                'deadline' => $wager->deadline->toIso8601String(),
                'status' => $wager->status,
                'participants_count' => $wager->participants_count,
                'total_points_wagered' => $wager->total_points_wagered,
                'is_creator' => $wager->creator_id === $user->id,
                'user_answer' => $userEntry?->answer_value,
                'user_points_wagered' => $userEntry?->points_wagered,
            ];

            if ($wager->deadline > $now) {
                $openWagers->push($wagerData);
            } else {
                $awaitingSettlement->push($wagerData);
            }
        }

        $activeWagers = $activeWagersQuery->map(function($wager) use ($user) {
            $userEntry = $wager->entries->firstWhere('user_id', $user->id);

            return [
                'id' => $wager->id,
                'title' => $wager->title,
                'url' => route('wager.show', ['wager' => $wager->id]),
                'group' => ['id' => $wager->group->id, 'name' => $wager->group->name],
                'type' => $wager->type,
                'stake_amount' => $wager->stake_amount,
                'deadline' => $wager->deadline->toIso8601String(),
                'status' => $wager->status,
                'participants_count' => $wager->participants_count,
                'total_points_wagered' => $wager->total_points_wagered,
                'is_creator' => $wager->creator_id === $user->id,
                'user_answer' => $userEntry?->answer_value,
                'user_points_wagered' => $userEntry?->points_wagered,
            ];
        });

        // Get user's recent settled wagers
        $settledWagers = Wager::where('status', 'settled')
            ->where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhereHas('entries', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['group', 'entries.user'])
            ->orderBy('settled_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($wager) use ($user) {
                $userEntry = $wager->entries->firstWhere('user_id', $user->id);

                return [
                    'id' => $wager->id,
                    'title' => $wager->title,
                    'url' => route('wager.show', ['wager' => $wager->id]),
                    'group' => ['id' => $wager->group->id, 'name' => $wager->group->name],
                    'type' => $wager->type,
                    'outcome_value' => $wager->outcome_value,
                    'settled_at' => $wager->settled_at?->toIso8601String(),
                    'is_creator' => $wager->creator_id === $user->id,
                    'user_answer' => $userEntry?->answer_value,
                    'user_points_wagered' => $userEntry?->points_wagered,
                    'is_winner' => $userEntry?->is_winner,
                    'payout_amount' => $userEntry?->payout_amount,
                ];
            });

        // Get recent transactions (already has eager loading)
        $transactionsQuery = Transaction::where('user_id', $user->id);

        if ($groupId) {
            $transactionsQuery->where('group_id', $groupId);
        }

        $recentTransactions = $transactionsQuery
            ->with(['group:id,name', 'wager:id,title'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($tx) => [
                'id' => $tx->id,
                'type' => $tx->type,
                'amount' => $tx->amount,
                'balance_before' => $tx->balance_before,
                'balance_after' => $tx->balance_after,
                'description' => $tx->description,
                'group' => $tx->group ? ['id' => $tx->group->id, 'name' => $tx->group->name] : null,
                'wager' => $tx->wager ? ['id' => $tx->wager->id, 'title' => $tx->wager->title] : null,
                'created_at' => $tx->created_at->toIso8601String(),
            ]);

        // Calculate stats
        $totalBalance = $groups->sum('balance');
        $activeWagersCount = $activeWagers->count();
        $totalWagersCount = Wager::where(function($query) use ($user) {
            $query->where('creator_id', $user->id)
                  ->orWhereHas('entries', function($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        })->count();

        $wonWagersCount = Wager::where('status', 'settled')
            ->whereHas('entries', function($q) use ($user) {
                $q->where('user_id', $user->id)->where('is_winner', true);
            })
            ->count();

        $winRate = $totalWagersCount > 0 ? round(($wonWagersCount / $totalWagersCount) * 100, 1) : 0;

        // Get Telegram username (platform-agnostic approach)
        $telegramService = $user->getTelegramService();

        return Inertia::render('Dashboard/Me', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'telegram_username' => $telegramService?->username,
                'taunt_line' => $user->taunt_line,
                'birthday' => $user->birthday?->format('Y-m-d'),
            ],
            'stats' => [
                'total_balance' => $totalBalance,
                'active_wagers' => $activeWagersCount,
                'win_rate' => $winRate,
                'total_wagers' => $totalWagersCount,
                'won_wagers' => $wonWagersCount,
            ],
            'groups' => $groups,
            'activeWagers' => $activeWagers,
            'openWagers' => $openWagers,
            'awaitingSettlement' => $awaitingSettlement,
            'settledWagers' => $settledWagers,
            'recentTransactions' => $recentTransactions,
            'focus' => $focus,
        ]);
    }

    /**
     * Update user profile settings
     *
     * Authentication handled by 'signed.auth' middleware - user is already authenticated via session
     */
    public function updateProfile(Request $request)
    {
        // Get authenticated user from session (middleware handles auth)
        $user = Auth::user();

        // Validate and update
        $validated = $request->validate([
            'taunt_line' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }
}
