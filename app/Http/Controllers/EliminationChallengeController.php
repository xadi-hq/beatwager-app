<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\User;
use App\Services\EliminationChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EliminationChallengeController extends Controller
{
    public function __construct(
        private readonly EliminationChallengeService $eliminationService
    ) {}

    /**
     * Show the tap-in confirmation page
     */
    public function showTapIn(Challenge $challenge): Response
    {
        // Signature already validated by middleware
        $user = Auth::user();
        $group = $challenge->group;

        if (!$challenge->isEliminationChallenge()) {
            abort(404, 'Challenge not found');
        }

        // Check if user can tap in
        $canTapIn = $challenge->status === 'open'
            && !$challenge->isTapInClosed()
            && !$challenge->participants()->where('user_id', $user->id)->exists();

        // Get user's current balance in this group
        $groupUser = $group->users()->where('user_id', $user->id)->first();
        $userBalance = $groupUser?->pivot->points ?? 0;

        return Inertia::render('Elimination/TapIn', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'elimination_trigger' => $challenge->elimination_trigger,
                'elimination_mode' => $challenge->elimination_mode?->value,
                'point_pot' => $challenge->point_pot,
                'buy_in_amount' => $challenge->buy_in_amount,
                'tap_in_deadline' => $challenge->tap_in_deadline?->toIso8601String(),
                'completion_deadline' => $challenge->completion_deadline?->toIso8601String(),
                'participant_count' => $challenge->participants()->count(),
                'min_participants' => $challenge->min_participants,
            ],
            'group' => [
                'id' => $group->id,
                'name' => $group->platform_chat_title ?: $group->name,
                'currency' => $group->points_currency_name ?? 'points',
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'balance' => $userBalance,
            ],
            'canTapIn' => $canTapIn,
            'alreadyParticipating' => $challenge->participants()->where('user_id', $user->id)->exists(),
        ]);
    }

    /**
     * Process tap-in for a user
     */
    public function tapIn(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = Auth::user();

        if (!$challenge->isEliminationChallenge()) {
            return back()->withErrors(['error' => 'Challenge not found']);
        }

        try {
            $participant = $this->eliminationService->tapIn($challenge, $user);

            return redirect()->route('elimination.tap-in.success', ['challenge' => $challenge->id])
                ->with('success', 'You have successfully tapped in!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show tap-in success page
     */
    public function tapInSuccess(Challenge $challenge): Response
    {
        $user = Auth::user();
        $participant = $challenge->participants()->where('user_id', $user->id)->first();

        if (!$participant) {
            abort(404, 'Participant not found');
        }

        return Inertia::render('Elimination/TapInSuccess', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'elimination_trigger' => $challenge->elimination_trigger,
                'elimination_mode' => $challenge->elimination_mode?->value,
                'point_pot' => $challenge->point_pot,
                'buy_in_amount' => $challenge->buy_in_amount,
                'completion_deadline' => $challenge->completion_deadline?->toIso8601String(),
                'participant_count' => $challenge->participants()->count(),
            ],
            'group' => [
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
                'currency' => $challenge->group->points_currency_name ?? 'points',
            ],
        ]);
    }

    /**
     * Show the tap-out confirmation page
     */
    public function showTapOut(Challenge $challenge): Response
    {
        $user = Auth::user();

        if (!$challenge->isEliminationChallenge()) {
            abort(404, 'Challenge not found');
        }

        /** @var ChallengeParticipant|null $participant */
        $participant = $challenge->participants()
            ->where('user_id', $user->id)
            ->whereNull('eliminated_at')
            ->first();

        if (!$participant) {
            abort(404, 'You are not an active participant in this challenge');
        }

        $survivorCount = $challenge->getSurvivors()->count();
        $eliminatedCount = $challenge->participants()->whereNotNull('eliminated_at')->count();

        return Inertia::render('Elimination/TapOut', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'elimination_trigger' => $challenge->elimination_trigger,
                'elimination_mode' => $challenge->elimination_mode?->value,
                'point_pot' => $challenge->point_pot,
                'survivor_count' => $survivorCount,
                'eliminated_count' => $eliminatedCount,
                'completion_deadline' => $challenge->completion_deadline?->toIso8601String(),
            ],
            'group' => [
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
                'currency' => $challenge->group->points_currency_name ?? 'points',
            ],
            'participant' => [
                'id' => $participant->id,
                'days_survived' => $participant->getDaysSurvived(),
            ],
        ]);
    }

    /**
     * Process tap-out for a user
     */
    public function tapOut(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = Auth::user();

        if (!$challenge->isEliminationChallenge()) {
            return back()->withErrors(['error' => 'Challenge not found']);
        }

        $validated = $request->validate([
            'elimination_note' => 'nullable|string|max:500',
        ]);

        try {
            $participant = $this->eliminationService->tapOut(
                $challenge,
                $user,
                $validated['elimination_note'] ?? null
            );

            return redirect()->route('elimination.tap-out.success', ['challenge' => $challenge->id])
                ->with('success', 'You have tapped out.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show tap-out confirmation page
     */
    public function tapOutSuccess(Challenge $challenge): Response
    {
        $user = Auth::user();

        /** @var ChallengeParticipant|null $participant */
        $participant = $challenge->participants()
            ->where('user_id', $user->id)
            ->whereNotNull('eliminated_at')
            ->first();

        if (!$participant) {
            abort(404, 'Participant not found');
        }

        return Inertia::render('Elimination/TapOutSuccess', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'elimination_trigger' => $challenge->elimination_trigger,
            ],
            'participant' => [
                'days_survived' => $participant->getDaysSurvived(),
                'eliminated_at' => $participant->eliminated_at?->toIso8601String(),
            ],
            'group' => [
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
            ],
        ]);
    }

    /**
     * Cancel an elimination challenge (creator only)
     */
    public function cancel(Request $request, Challenge $challenge): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$challenge->isEliminationChallenge()) {
            return back()->withErrors(['error' => 'Challenge not found']);
        }

        // Only creator can cancel (cast to string for PHPStan compatibility with UUIDs)
        if ((string) $challenge->creator_id !== (string) $user->id) {
            return back()->withErrors(['error' => 'Only the challenge creator can cancel this challenge']);
        }

        try {
            $this->eliminationService->cancel($challenge, $user);

            return redirect()->route('elimination.cancelled', ['challenge' => $challenge->id])
                ->with('success', 'Challenge cancelled successfully. Buy-ins have been refunded.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show cancellation confirmation page
     */
    public function cancelled(Challenge $challenge): Response
    {
        return Inertia::render('Elimination/Cancelled', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'cancelled_at' => $challenge->cancelled_at?->toIso8601String(),
            ],
            'group' => [
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
            ],
        ]);
    }

    /**
     * Show elimination challenge details
     */
    public function show(Challenge $challenge): Response
    {
        if (!$challenge->isEliminationChallenge()) {
            abort(404, 'Challenge not found');
        }

        /** @var User $user */
        $user = Auth::user();
        $challenge->load(['creator', 'group', 'participants.user']);

        $participant = $challenge->participants->where('user_id', $user->id)->first();

        return Inertia::render('Elimination/Show', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'elimination_trigger' => $challenge->elimination_trigger,
                'elimination_mode' => $challenge->elimination_mode?->value,
                'status' => $challenge->status,
                'point_pot' => $challenge->point_pot,
                'buy_in_amount' => $challenge->buy_in_amount,
                'tap_in_deadline' => $challenge->tap_in_deadline?->toIso8601String(),
                'completion_deadline' => $challenge->completion_deadline?->toIso8601String(),
                'min_participants' => $challenge->min_participants,
                'created_at' => $challenge->created_at->toIso8601String(),
                'completed_at' => $challenge->completed_at?->toIso8601String(),
                'cancelled_at' => $challenge->cancelled_at?->toIso8601String(),
            ],
            'creator' => [
                'id' => $challenge->creator->id,
                'name' => $challenge->creator->name,
            ],
            'group' => [
                'id' => $challenge->group->id,
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
                'currency' => $challenge->group->points_currency_name ?? 'points',
            ],
            'participants' => $challenge->participants->map(fn($p) => [
                'id' => $p->id,
                'user_name' => $p->user->name ?? 'Unknown',
                'is_eliminated' => $p->eliminated_at !== null,
                'eliminated_at' => $p->eliminated_at?->toIso8601String(),
                'days_survived' => $p->getDaysSurvived(),
            ])->values(),
            'userParticipant' => $participant ? [
                'id' => $participant->id,
                'is_eliminated' => $participant->eliminated_at !== null,
                'days_survived' => $participant->getDaysSurvived(),
            ] : null,
            'isCreator' => (string) $challenge->creator_id === (string) $user->id,
            'canTapIn' => $challenge->status === 'open'
                && !$challenge->isTapInClosed()
                && !$participant,
            'canTapOut' => $participant
                && $participant->eliminated_at === null
                && $challenge->status === 'open',
            'canCancel' => (string) $challenge->creator_id === (string) $user->id
                && $challenge->status === 'open',
        ]);
    }
}
