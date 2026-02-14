<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\GroupEvent;
use App\Models\OneTimeToken;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\Wager;
use App\Models\WagerEntry;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {}
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

        // Get user's groups with balances and leaderboard data
        $groups = $user->groups()->get()->map(function($g) {
            // Get all members with their points for leaderboard
            $members = $g->users()
                ->withPivot(['points', 'points_earned', 'points_spent'])
                ->orderByPivot('points', 'desc')
                ->get()
                ->map(fn($member, $index) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'points' => $member->pivot->points,
                    'points_earned' => $member->pivot->points_earned,
                    'points_spent' => $member->pivot->points_spent,
                    'rank' => $index + 1,
                ]);

            return [
                'id' => $g->id,
                'name' => $g->name,
                'balance' => $g->pivot->points,
                'role' => $g->pivot->role,
                'currency' => $g->points_currency_name ?? 'points',
                'house_pot' => $g->house_pot ?? 0,
                'leaderboard' => $members,
            ];
        });

        // Get user's active wagers (created or joined) - split by deadline
        // Creators always see their own wagers; joined wagers filtered by active criteria
        $activeWagersQuery = Wager::where('status', 'open')
            ->where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhereHas('entries', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->where(function($query) use ($user) {
                // Creator always sees their own wagers, even with 0 participants past deadline
                $query->where('creator_id', $user->id)
                      ->orWhere('betting_closes_at', '>=', now())
                      ->orWhere('participants_count', '>', 0);
            })
            ->with(['group', 'entries.user'])
            ->orderBy('betting_closes_at', 'asc')
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
                'group' => [
                    'id' => $wager->group->id,
                    'name' => $wager->group->name,
                    'currency' => $wager->group->points_currency_name ?? 'points',
                ],
                'type' => $wager->type,
                'stake_amount' => $wager->stake_amount,
                'betting_closes_at' => $wager->betting_closes_at->toIso8601String(),
                'expected_settlement_at' => $wager->expected_settlement_at?->toIso8601String(),
                'status' => $wager->status,
                'participants_count' => $wager->participants_count,
                'total_points_wagered' => $wager->total_points_wagered,
                'is_creator' => $wager->creator_id === $user->id,
                'user_answer' => $wager->getAnswerDisplayLabel($userEntry?->answer_value),
                'user_points_wagered' => $userEntry?->points_wagered,
            ];

            if ($wager->betting_closes_at > $now) {
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
                'betting_closes_at' => $wager->betting_closes_at->toIso8601String(),
                'expected_settlement_at' => $wager->expected_settlement_at?->toIso8601String(),
                'status' => $wager->status,
                'participants_count' => $wager->participants_count,
                'total_points_wagered' => $wager->total_points_wagered,
                'is_creator' => $wager->creator_id === $user->id,
                'user_answer' => $wager->getAnswerDisplayLabel($userEntry?->answer_value),
                'user_points_wagered' => $userEntry?->points_wagered,
            ];
        });

        // Get user's recent settled wagers (including disputed ones)
        $settledWagers = Wager::whereIn('status', ['settled', 'disputed'])
            ->where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhereHas('entries', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['group', 'entries.user', 'dispute'])
            ->orderByRaw("CASE WHEN status = 'disputed' THEN 0 ELSE 1 END") // Disputed first
            ->orderBy('settled_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($wager) use ($user) {
                $userEntry = $wager->entries->firstWhere('user_id', $user->id);

                return [
                    'id' => $wager->id,
                    'title' => $wager->title,
                    'url' => route('wager.show', ['wager' => $wager->id]),
                    'group' => [
                        'id' => $wager->group->id,
                        'name' => $wager->group->name,
                        'currency' => $wager->group->points_currency_name ?? 'points',
                    ],
                    'type' => $wager->type,
                    'status' => $wager->status,
                    'outcome_value' => $wager->getAnswerDisplayLabel($wager->outcome_value),
                    'settled_at' => $wager->settled_at?->toIso8601String(),
                    'is_creator' => $wager->creator_id === $user->id,
                    'user_answer' => $wager->getAnswerDisplayLabel($userEntry?->answer_value),
                    'user_points_wagered' => $userEntry?->points_wagered,
                    'is_winner' => $userEntry?->is_winner,
                    'points_won' => $userEntry?->points_won,
                    'result' => $userEntry?->result,
                    'dispute' => $wager->dispute ? [
                        'id' => $wager->dispute->id,
                        'status' => $wager->dispute->status->value,
                        'resolution' => $wager->dispute->resolution?->value,
                    ] : null,
                ];
            });

        // Get recent transactions (already has eager loading)
        $transactionsQuery = Transaction::where('user_id', $user->id);

        if ($groupId) {
            $transactionsQuery->where('group_id', $groupId);
        }

        $recentTransactions = $transactionsQuery
            ->with([
                'group:id,name,points_currency_name',
                'transactionable' => function ($query) {
                    $query->morphWith([
                        WagerEntry::class => ['wager'],
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($tx) {
                // Get wager from WagerEntry if transactionable is a WagerEntry
                $wager = null;
                if ($tx->transactionable instanceof WagerEntry) {
                    $wager = $tx->transactionable->wager;
                }

                return [
                    'id' => $tx->id,
                    'type' => $tx->type,
                    'amount' => $tx->amount,
                    'balance_before' => $tx->balance_before,
                    'balance_after' => $tx->balance_after,
                    'description' => $tx->description,
                    'currency' => $tx->group ? ($tx->group->points_currency_name ?? 'points') : 'points',
                    'group' => $tx->group ? ['id' => $tx->group->id, 'name' => $tx->group->name] : null,
                    'wager' => $wager ? ['id' => $wager->id, 'title' => $wager->title] : null,
                    'created_at' => $tx->created_at->toIso8601String(),
                ];
            });

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

        // Load events for all user's groups
        $upcomingEvents = collect();
        $pastProcessedEvents = collect();
        $pastUnprocessedEvents = collect();

        foreach ($user->groups as $group) {
            $groupEvents = $this->eventService->getEventsForGroup($group);

            // Transform events to match frontend expectations
            $transformEvent = function($event) {
                $rsvpCounts = $this->eventService->getRsvpCounts($event);
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'event_date' => $event->event_date->toIso8601String(),
                    'location' => $event->location,
                    'attendance_bonus' => $event->attendance_bonus,
                    'status' => $event->status,
                    'group' => [
                        'id' => $event->group->id,
                        'name' => $event->group->name ?? $event->group->platform_chat_title,
                    ],
                    'rsvps' => [
                        'going' => $rsvpCounts['going'],
                        'maybe' => $rsvpCounts['maybe'],
                        'not_going' => $rsvpCounts['not_going'],
                    ],
                    'url' => route('events.show', $event->id),
                ];
            };

            $upcomingEvents = $upcomingEvents->merge($groupEvents['upcoming']->map($transformEvent));
            $pastProcessedEvents = $pastProcessedEvents->merge($groupEvents['past_processed']->map($transformEvent));
            $pastUnprocessedEvents = $pastUnprocessedEvents->merge($groupEvents['past_unprocessed']->map($transformEvent));
        }

        // Sort events by date
        $upcomingEvents = $upcomingEvents->sortBy('event_date')->values();
        $pastProcessedEvents = $pastProcessedEvents->sortByDesc('event_date')->values();
        $pastUnprocessedEvents = $pastUnprocessedEvents->sortByDesc('event_date')->values();

        // Load challenges (created by, accepted by, or participating in)
        // Using active() scope to exclude expired challenges with no acceptor
        $userChallenges = Challenge::active()
            ->where(function($q) use ($user) {
                // 1-on-1 challenges: created by or accepted by user
                $q->where('creator_id', $user->id)
                  ->orWhere('acceptor_id', $user->id)
                  // SuperChallenges or Elimination: user is a participant
                  ->orWhereHas('participants', function($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  })
                  // Open SuperChallenges in user's groups (available to accept)
                  ->orWhere(function($q3) use ($user) {
                      $q3->where('type', \App\Enums\ChallengeType::SUPER_CHALLENGE)
                         ->where('status', 'open')
                         ->whereIn('group_id', function($q4) use ($user) {
                             $q4->select('group_id')
                                ->from('group_user')
                                ->where('user_id', $user->id);
                         });
                  })
                  // Open Elimination Challenges in user's groups (available to tap in)
                  ->orWhere(function($q3) use ($user) {
                      $q3->where('type', \App\Enums\ChallengeType::ELIMINATION_CHALLENGE)
                         ->where('status', 'open')
                         ->whereIn('group_id', function($q4) use ($user) {
                             $q4->select('group_id')
                                ->from('group_user')
                                ->where('user_id', $user->id);
                         });
                  });
            })
            ->with(['group', 'creator', 'acceptor', 'participants.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($c) use ($user) {
                $isParticipant = ($c->isSuperChallenge() || $c->isEliminationChallenge())
                    && $c->participants->contains('user_id', $user->id);

                // For elimination challenges, get user's participant record
                $userParticipant = $c->isEliminationChallenge()
                    ? $c->participants->firstWhere('user_id', $user->id)
                    : null;

                // Determine the appropriate URL based on challenge type
                $url = $c->isEliminationChallenge()
                    ? route('elimination.show', $c->id)
                    : route('challenges.show', $c->id);

                // For elimination challenges, determine effective status for display
                $effectiveStatus = $c->status;
                if ($c->isEliminationChallenge()) {
                    if ($isParticipant) {
                        $effectiveStatus = $userParticipant?->eliminated_at ? 'eliminated' : 'active';
                    } else {
                        $effectiveStatus = 'open'; // Available to tap in
                    }
                } elseif ($c->isSuperChallenge()) {
                    $effectiveStatus = $isParticipant ? 'accepted' : 'open';
                }

                return [
                    'id' => $c->id,
                    'description' => $c->description,
                    'amount' => $c->isSuperChallenge() ? $c->prize_per_person : ($c->isEliminationChallenge() ? $c->point_pot : $c->amount),
                    'type' => $c->type?->value,
                    'status' => $effectiveStatus,
                    'completion_deadline' => $c->completion_deadline?->toIso8601String(),
                    'acceptance_deadline' => $c->acceptance_deadline?->toIso8601String(),
                    'tap_in_deadline' => $c->tap_in_deadline?->toIso8601String(),
                    'accepted_at' => $c->accepted_at?->toIso8601String(),
                    'submitted_at' => $c->submitted_at?->toIso8601String(),
                    'completed_at' => $c->completed_at?->toIso8601String(),
                    'failed_at' => $c->failed_at?->toIso8601String(),
                    'is_creator' => $c->creator_id === $user->id,
                    'is_acceptor' => $c->acceptor_id === $user->id || $isParticipant,
                    'is_participant' => $isParticipant,
                    // Elimination-specific fields
                    'elimination_mode' => $c->elimination_mode,
                    'elimination_trigger' => $c->elimination_trigger,
                    'buy_in_amount' => $c->buy_in_amount,
                    'participants_count' => $c->isEliminationChallenge() ? $c->participants->count() : null,
                    'survivors_count' => $c->isEliminationChallenge() ? $c->participants->whereNull('eliminated_at')->count() : null,
                    'user_eliminated' => $userParticipant?->eliminated_at !== null,
                    'group' => [
                        'id' => $c->group->id,
                        'name' => $c->group->name ?? $c->group->platform_chat_title,
                    ],
                    'creator' => [
                        'id' => $c->creator->id,
                        'name' => $c->creator->name,
                    ],
                    'acceptor' => $c->acceptor ? [
                        'id' => $c->acceptor->id,
                        'name' => $c->acceptor->name,
                    ] : null,
                    'url' => $url,
                ];
            });

        // Calculate active challenges (user is involved and not completed/failed/cancelled)
        $activeChallengesCount = $userChallenges->filter(function($c) {
            return in_array($c['status'], ['open', 'accepted', 'active']);
        })->count();

        // Calculate upcoming events count
        $upcomingEventsCount = $upcomingEvents->count();

        // Load user badges
        $userBadges = UserBadge::where('user_id', $user->id)
            ->active()
            ->with(['badge', 'group'])
            ->recent()
            ->get()
            ->map(function ($userBadge) {
                return [
                    'id' => $userBadge->id,
                    'badge' => [
                        'id' => $userBadge->badge->id,
                        'slug' => $userBadge->badge->slug,
                        'name' => $userBadge->badge->name,
                        'description' => $userBadge->badge->description,
                        'category' => $userBadge->badge->category->value,
                        'tier' => $userBadge->badge->tier->value,
                        'is_shame' => $userBadge->badge->is_shame,
                        'image_url' => $userBadge->badge->imageUrl,
                        'small_image_url' => $userBadge->badge->smallImageUrl,
                    ],
                    'group_id' => $userBadge->group_id,
                    'group_name' => $userBadge->group?->name,
                    'awarded_at' => $userBadge->awarded_at->toIso8601String(),
                ];
            });

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
                'active_challenges' => $activeChallengesCount,
                'upcoming_events' => $upcomingEventsCount,
                'active_items' => $activeWagersCount + $activeChallengesCount + $upcomingEventsCount,
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
            'upcomingEvents' => $upcomingEvents,
            'pastProcessedEvents' => $pastProcessedEvents,
            'pastUnprocessedEvents' => $pastUnprocessedEvents,
            'userChallenges' => $userChallenges,
            'userBadges' => $userBadges,
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
