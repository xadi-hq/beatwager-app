<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EliminationMode;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use App\Services\ChallengeService;
use App\Services\EliminationChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function __construct(
        private readonly ChallengeService $challengeService,
        private readonly EliminationChallengeService $eliminationService
    ) {}

    /**
     * Show the challenge creation form
     */
    public function create(Request $request): Response
    {
        // Signature already validated by middleware
        // User already authenticated by middleware
        $user = Auth::user();

        // Get or create group from URL parameters (if from a group chat)
        $group = $this->getOrCreateGroup($request, $user);

        // Get user's groups for dropdown - only show actual groups (negative chat IDs for Telegram)
        /** @var \Illuminate\Database\Eloquent\Collection<int, Group> $groups */
        $groups = $user->groups()
            ->where(function ($query) {
                $query->where('platform', 'telegram')->where('platform_chat_id', '<', '0')
                    ->orWhere('platform', '!=', 'telegram'); // Non-Telegram groups don't have negative ID constraint
            })
            ->get();

        $userGroups = $groups->map(fn (Group $g) => [
            'id' => $g->id,
            'name' => $g->platform_chat_title ?: $g->name,
        ]);

        // Get Telegram username for display
        $telegramService = $user->getTelegramService();

        // If no groups available, set default to null
        $defaultGroup = $group && ($group->platform !== 'telegram' || (int)$group->platform_chat_id < 0) ? [
            'id' => $group->id,
            'name' => $group->platform_chat_title ?: $group->name,
        ] : null;

        // Calculate elimination challenge defaults if group is available
        $eliminationDefaults = null;
        if ($group) {
            $suggestedPot = $this->eliminationService->calculateSuggestedPot($group);
            $memberCount = max($group->users()->count(), 3);
            $suggestedBuyIn = $this->eliminationService->calculateBuyIn($suggestedPot, $memberCount);

            // Determine season end date if group has active season
            $seasonEndsAt = null;
            if ($group->current_season_id) {
                /** @var \Carbon\Carbon|null $seasonEnd */
                $seasonEnd = $group->season_ends_at;
                if ($seasonEnd instanceof \Carbon\Carbon && $seasonEnd->isFuture()) {
                    $seasonEndsAt = $seasonEnd->toIso8601String();
                }
            }

            $eliminationDefaults = [
                'suggested_pot' => $suggestedPot,
                'suggested_buy_in' => $suggestedBuyIn,
                'total_group_currency' => $this->eliminationService->getTotalGroupCurrency($group),
                'currency_name' => $group->points_currency_name ?? 'points',
                // If group has active season, suggest season end as default deadline
                'season_ends_at' => $seasonEndsAt,
            ];
        }

        return Inertia::render('Challenge/Create', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'telegram_username' => $telegramService?->username,
                'balance' => $group ? $this->getUserBalanceInGroup($user, $group) : null,
            ],
            'defaultGroup' => $defaultGroup,
            'groups' => $userGroups,
            'groupMembers' => $group ? $this->getGroupMembersForDisplay($group) : [],
            'eliminationDefaults' => $eliminationDefaults,
            'eliminationMinParticipants' => config('features.elimination_min_participants'),
        ]);
    }

    /**
     * Store a new challenge
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'amount' => 'required|integer|min:1|max:10000',
            'group_id' => 'required|uuid|exists:groups,id',
            'completion_deadline' => 'required|date|after:now',
            'acceptance_deadline' => 'nullable|date|after:now|before:completion_deadline',
        ]);

        // User is already authenticated by middleware
        $user = Auth::user();
        $group = Group::findOrFail($validated['group_id']);

        // Convert datetime fields from group timezone to UTC for storage
        $validated['completion_deadline'] = $group->toUtc($validated['completion_deadline']);
        if (!empty($validated['acceptance_deadline'])) {
            $validated['acceptance_deadline'] = $group->toUtc($validated['acceptance_deadline']);
        }

        // Create challenge
        $challenge = $this->challengeService->createChallenge($group, $user, $validated);
        $challenge->load("creator");

        // Event dispatched in ChallengeService

        return redirect()->route('challenge.success', ['challenge' => $challenge->id]);
    }

    /**
     * Show success page
     */
    public function success(Challenge $challenge): Response
    {
        return Inertia::render('Challenge/Success', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'amount' => $challenge->amount,
                'completion_deadline' => $challenge->completion_deadline->toIso8601String(),
                'acceptance_deadline' => $challenge->acceptance_deadline?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Show challenge details
     */
    public function show(Challenge $challenge): Response
    {
        $challenge->load(['creator', 'acceptor', 'group', 'verifiedBy', 'cancelledBy']);

        // Get user's role in this challenge
        /** @var User $user */
        $user = Auth::user();
        /** @var string $userId */
        $userId = $user->id;
        $userRole = 'viewer';
        if ($challenge->creator_id === $userId) {
            $userRole = 'creator';
        } elseif ($challenge->acceptor_id === $userId) {
            $userRole = 'acceptor';
        }

        return Inertia::render('Challenge/Show', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'amount' => $challenge->amount,
                'status' => $challenge->status,
                'completion_deadline' => $challenge->completion_deadline->toIso8601String(),
                'acceptance_deadline' => $challenge->acceptance_deadline?->toIso8601String(),
                'accepted_at' => $challenge->accepted_at?->toIso8601String(),
                'submitted_at' => $challenge->submitted_at?->toIso8601String(),
                'completed_at' => $challenge->completed_at?->toIso8601String(),
                'failed_at' => $challenge->failed_at?->toIso8601String(),
                'cancelled_at' => $challenge->cancelled_at?->toIso8601String(),
                'submission_note' => $challenge->submission_note,
                'failure_reason' => $challenge->failure_reason,
                'currency' => $challenge->group->points_currency_name ?? 'points',
            ],
            'creator' => [
                'id' => $challenge->creator->id,
                'name' => $challenge->creator->name,
            ],
            'acceptor' => $challenge->acceptor ? [
                'id' => $challenge->acceptor->id,
                'name' => $challenge->acceptor->name,
            ] : null,
            'group' => [
                'id' => $challenge->group->id,
                'name' => $challenge->group->name ?? $challenge->group->platform_chat_title,
            ],
            'userRole' => $userRole,
        ]);
    }

    /**
     * Accept a challenge
     */
    public function accept(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = Auth::user();

        try {
            $acceptedChallenge = $this->challengeService->acceptChallenge($challenge, $user);

            return back()->with([
                'success' => 'Challenge accepted successfully!',
                'challenge' => [
                    'id' => $acceptedChallenge->id,
                    'status' => $acceptedChallenge->status,
                    'acceptor' => [
                        'id' => $acceptedChallenge->acceptor->id,
                        'name' => $acceptedChallenge->acceptor->name,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Submit challenge completion
     */
    public function submit(Request $request, Challenge $challenge): RedirectResponse
    {
        $validated = $request->validate([
            'submission_note' => 'nullable|string|max:1000',
            'submission_media' => 'nullable|array',
        ]);

        $user = Auth::user();

        try {
            $submittedChallenge = $this->challengeService->submitChallenge(
                $challenge,
                $user,
                $validated['submission_note'] ?? null,
                $validated['submission_media'] ?? null
            );

            return back()->with([
                'success' => 'Challenge submitted for review!',
                'challenge' => [
                    'id' => $submittedChallenge->id,
                    'status' => $submittedChallenge->status,
                    'submitted_at' => $submittedChallenge->submitted_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve challenge completion
     */
    public function approve(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = Auth::user();

        try {
            $approvedChallenge = $this->challengeService->approveChallenge($challenge, $user);

            return back()->with([
                'success' => 'Challenge approved! Points have been transferred.',
                'challenge' => [
                    'id' => $approvedChallenge->id,
                    'status' => $approvedChallenge->status,
                    'completed_at' => $approvedChallenge->completed_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reject challenge completion
     */
    public function reject(Request $request, Challenge $challenge): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        try {
            $rejectedChallenge = $this->challengeService->rejectChallenge(
                $challenge,
                $user,
                $validated['reason']
            );

            return back()->with([
                'success' => 'Challenge rejected. Points have been returned to creator.',
                'challenge' => [
                    'id' => $rejectedChallenge->id,
                    'status' => $rejectedChallenge->status,
                    'failed_at' => $rejectedChallenge->failed_at->toIso8601String(),
                    'failure_reason' => $rejectedChallenge->failure_reason,
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cancel an open challenge
     */
    public function cancel(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = Auth::user();

        try {
            $cancelledChallenge = $this->challengeService->cancelChallenge($challenge, $user);

            return back()->with([
                'success' => 'Challenge cancelled successfully.',
                'challenge' => [
                    'id' => $cancelledChallenge->id,
                    'status' => $cancelledChallenge->status,
                    'cancelled_at' => $cancelledChallenge->cancelled_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store a new elimination challenge
     */
    public function storeElimination(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:200',
            'elimination_trigger' => 'required|string|max:500',
            'elimination_mode' => 'required|string|in:last_man_standing,deadline',
            'group_id' => 'required|uuid|exists:groups,id',
            'point_pot' => 'required|integer|min:100|max:1000000',
            'completion_deadline' => 'nullable|date|after:now',
            'tap_in_deadline' => 'nullable|date|after:now',
            'min_participants' => 'nullable|integer|min:' . config('features.elimination_min_participants') . '|max:100',
        ]);

        // Deadline mode requires completion_deadline
        if ($validated['elimination_mode'] === 'deadline' && empty($validated['completion_deadline'])) {
            return back()->withErrors([
                'completion_deadline' => 'Deadline mode requires a completion deadline.',
            ]);
        }

        // Validate tap_in_deadline is before completion_deadline
        if (!empty($validated['tap_in_deadline']) && !empty($validated['completion_deadline'])) {
            $tapIn = \Carbon\Carbon::parse($validated['tap_in_deadline']);
            $completion = \Carbon\Carbon::parse($validated['completion_deadline']);
            if ($tapIn->gte($completion)) {
                return back()->withErrors([
                    'tap_in_deadline' => 'Tap-in deadline must be before the completion deadline.',
                ]);
            }
        }

        $user = Auth::user();
        $group = Group::findOrFail($validated['group_id']);

        // Validate pot is within reasonable bounds for this group
        $suggestedPot = $this->eliminationService->calculateSuggestedPot($group);
        $maxAllowedPot = (int) ($suggestedPot * 3); // Allow up to 3x suggested
        $minAllowedPot = (int) max(100, $suggestedPot * 0.1); // At least 10% of suggested or 100

        if ($validated['point_pot'] > $maxAllowedPot) {
            return back()->withErrors([
                'point_pot' => "Pot cannot exceed {$maxAllowedPot} points for this group.",
            ]);
        }

        if ($validated['point_pot'] < $minAllowedPot) {
            return back()->withErrors([
                'point_pot' => "Pot must be at least {$minAllowedPot} points.",
            ]);
        }

        // Convert datetime fields from group timezone to UTC
        $completionDeadline = null;
        $tapInDeadline = null;

        if (!empty($validated['completion_deadline'])) {
            $completionDeadline = $group->toUtc($validated['completion_deadline']);
        }
        if (!empty($validated['tap_in_deadline'])) {
            $tapInDeadline = $group->toUtc($validated['tap_in_deadline']);
        }

        try {
            $challenge = $this->eliminationService->createChallenge(
                $group,
                $user,
                $validated['description'],
                $validated['elimination_trigger'],
                EliminationMode::from($validated['elimination_mode']),
                $completionDeadline,
                $validated['point_pot'],
                $tapInDeadline,
                $validated['min_participants'] ?? config('features.elimination_min_participants')
            );

            return redirect()->route('elimination.success', ['challenge' => $challenge->id]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show elimination challenge success page
     */
    public function eliminationSuccess(Challenge $challenge): Response
    {
        if (!$challenge->isEliminationChallenge()) {
            abort(404);
        }

        return Inertia::render('Challenge/EliminationSuccess', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'elimination_trigger' => $challenge->elimination_trigger,
                'elimination_mode' => $challenge->elimination_mode?->value,
                'point_pot' => $challenge->point_pot,
                'buy_in_amount' => $challenge->buy_in_amount,
                'completion_deadline' => $challenge->completion_deadline?->toIso8601String(),
                'tap_in_deadline' => $challenge->tap_in_deadline?->toIso8601String(),
            ],
            'group' => [
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
            ],
        ]);
    }

    /**
     * Get or create group from platform chat data (platform-agnostic)
     * Copied from WagerController
     */
    private function getOrCreateGroup(Request $request, User $user): ?Group
    {
        $chatId = $request->query('chat_id');
        if (!$chatId) {
            return null; // No group context (private chat)
        }

        // Get platform from user's primary messenger service
        $messengerService = $user->messengerServices()->where('is_primary', true)->first();
        if (!$messengerService) {
            // Fallback to any messenger service
            $messengerService = $user->messengerServices()->first();
        }

        $platform = $messengerService->platform ?? 'telegram';

        $group = Group::firstOrCreate(
            [
                'platform' => $platform,
                'platform_chat_id' => (string) $chatId,
            ],
            [
                'name' => $request->query('chat_title') ?? 'Chat ' . $chatId,
                'platform_chat_title' => $request->query('chat_title'),
                'platform_chat_type' => $request->query('chat_type'),
                'timezone' => 'Europe/Amsterdam',  // Default timezone for new groups
            ]
        );

        // Ensure user is in the group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => $group->starting_balance ?? 1000,
                'role' => 'participant',
            ]);
        }

        return $group;
    }

    /**
     * Get group members formatted for display
     *
     * @return array<int, array{name: string, points: int}>
     */
    private function getGroupMembersForDisplay(Group $group): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, User> $users */
        $users = $group->users()
            ->select('users.id', 'users.name', 'group_user.points')
            ->get();

        $members = [];
        foreach ($users as $user) {
            /** @var \App\Models\UserGroup $pivot */
            $pivot = $user->getRelation('pivot');
            $members[] = [
                'name' => $user->name,
                'points' => (int) $pivot->points,
            ];
        }

        // Sort by points ascending
        usort($members, fn (array $a, array $b) => $a['points'] <=> $b['points']);

        return $members;
    }

    /**
     * Get user's balance in a specific group
     */
    private function getUserBalanceInGroup(User $user, Group $group): int
    {
        $member = $group->users()
            ->where('users.id', $user->id)
            ->first();

        if ($member === null) {
            return 0;
        }

        /** @var \App\Models\UserGroup $pivot */
        $pivot = $member->getRelation('pivot');

        return (int) $pivot->points;
    }
}
