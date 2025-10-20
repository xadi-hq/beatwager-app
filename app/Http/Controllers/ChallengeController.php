<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function __construct(
        private readonly ChallengeService $challengeService
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
        $userGroups = $user->groups()
            ->where(function($query) {
                $query->where('platform', 'telegram')->where('platform_chat_id', '<', '0')
                    ->orWhere('platform', '!=', 'telegram'); // Non-Telegram groups don't have negative ID constraint
            })
            ->get()
            ->map(fn($g) => [
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

        return Inertia::render('Challenge/Create', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'telegram_username' => $telegramService?->username,
            ],
            'defaultGroup' => $defaultGroup,
            'groups' => $userGroups,
            'groupMembers' => $group ? $group->users()->select('users.id', 'users.name', 'group_user.points')->get()->map(fn($u) => ['name' => $u->name, 'points' => $u->pivot->points])->sortBy('points')->values() : [],
        ]);
    }

    /**
     * Store a new challenge
     */
    public function store(Request $request)
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

        // Create challenge
        $challenge = $this->challengeService->createChallenge($group, $user, $validated);
        $challenge->load("creator");

        // Event dispatched in ChallengeService

        return redirect()->route('challenge.success', ['challenge' => $challenge->id]);
    }

    /**
     * Show success page
     */
    public function success(Challenge $challenge)
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
        $user = Auth::user();
        $userRole = 'viewer';
        if ($challenge->creator_id === $user->id) {
            $userRole = 'creator';
        } elseif ($challenge->acceptor_id === $user->id) {
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
    public function accept(Request $request, Challenge $challenge)
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
    public function submit(Request $request, Challenge $challenge)
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
    public function approve(Request $request, Challenge $challenge)
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
    public function reject(Request $request, Challenge $challenge)
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
    public function cancel(Request $request, Challenge $challenge)
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
}
