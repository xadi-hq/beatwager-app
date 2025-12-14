<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DisputeVoteOutcome;
use App\Models\Challenge;
use App\Models\Dispute;
use App\Models\User;
use App\Models\Wager;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService
    ) {}

    /**
     * Show dispute details and voting interface
     */
    public function show(Dispute $dispute): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $dispute->load(['group', 'reporter', 'accused', 'disputable', 'votes.voter']);

        // Get the disputable item details
        $item = $dispute->disputable;
        $itemType = $item instanceof Wager ? 'wager' : 'elimination';
        $itemTitle = $item instanceof Wager ? $item->title : $item->description;

        // Get vote tally
        $voteTally = $dispute->getVoteTally();

        // Check if user can vote
        $canVote = $dispute->canUserVote($user);

        // Get user's existing vote if any
        $userVote = $dispute->votes->where('voter_id', $user->id)->first();

        // Get available outcome options for this dispute type
        $outcomeOptions = method_exists($item, 'getDisputeOutcomeOptions')
            ? $item->getDisputeOutcomeOptions()
            : ['correct', 'incorrect'];

        return Inertia::render('Dispute/Show', [
            'dispute' => [
                'id' => $dispute->id,
                'status' => $dispute->status->value,
                'resolution' => $dispute->resolution?->value,
                'original_outcome' => $dispute->original_outcome,
                'corrected_outcome' => $dispute->corrected_outcome,
                'is_self_report' => $dispute->is_self_report,
                'votes_required' => $dispute->votes_required,
                'vote_count' => $dispute->getVoteCount(),
                'expires_at' => $dispute->expires_at->toIso8601String(),
                'resolved_at' => $dispute->resolved_at?->toIso8601String(),
                'created_at' => $dispute->created_at->toIso8601String(),
                'time_remaining' => $dispute->getTimeRemaining(),
            ],
            'item' => [
                'id' => $item->id,
                'type' => $itemType,
                'title' => $itemTitle,
            ],
            'reporter' => [
                'id' => $dispute->reporter->id,
                'name' => $dispute->reporter->name,
            ],
            'accused' => [
                'id' => $dispute->accused->id,
                'name' => $dispute->accused->name,
            ],
            'group' => [
                'id' => $dispute->group->id,
                'name' => $dispute->group->platform_chat_title ?: $dispute->group->name,
                'currency' => $dispute->group->points_currency_name ?? 'points',
            ],
            'votes' => $dispute->votes->map(fn($vote) => [
                'id' => $vote->id,
                'voter_name' => $vote->voter->name,
                'vote_outcome' => $vote->vote_outcome->value,
                'vote_outcome_label' => $vote->vote_outcome->label(),
                'selected_outcome' => $vote->selected_outcome,
                'created_at' => $vote->created_at->toIso8601String(),
            ]),
            'voteTally' => $voteTally,
            'outcomeOptions' => $outcomeOptions,
            'canVote' => $canVote,
            'userVote' => $userVote ? [
                'vote_outcome' => $userVote->vote_outcome->value,
                'selected_outcome' => $userVote->selected_outcome,
            ] : null,
            'isReporter' => $user->id === $dispute->reporter_id,
            'isAccused' => $user->id === $dispute->accused_id,
        ]);
    }

    /**
     * Create a dispute for a wager
     */
    public function createWagerDispute(Request $request, Wager $wager): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Validate wager can be disputed
        if (!$wager->canBeDisputed()) {
            return back()->withErrors(['error' => 'This wager cannot be disputed.']);
        }

        try {
            $dispute = $this->disputeService->createDispute($wager, $user);

            return redirect()->route('disputes.show', ['dispute' => $dispute->id])
                ->with('success', 'Dispute created successfully. Group members can now vote.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create dispute. Please try again.']);
        }
    }

    /**
     * Create a dispute for an elimination challenge participant
     */
    public function createEliminationDispute(Request $request, Challenge $challenge): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Validate this is an elimination challenge
        if (!$challenge->isEliminationChallenge()) {
            return back()->withErrors(['error' => 'This is not an elimination challenge.']);
        }

        // Validate challenge can be disputed
        if (!$challenge->canBeDisputed()) {
            return back()->withErrors(['error' => 'This challenge cannot be disputed.']);
        }

        // Validate accused participant is provided
        $validated = $request->validate([
            'accused_user_id' => 'required|uuid|exists:users,id',
        ]);

        $accusedUser = User::findOrFail($validated['accused_user_id']);

        try {
            $dispute = $this->disputeService->createEliminationDispute(
                $challenge,
                $user,
                $accusedUser
            );

            return redirect()->route('disputes.show', ['dispute' => $dispute->id])
                ->with('success', 'Dispute created successfully. Group members can now vote.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create dispute. Please try again.']);
        }
    }

    /**
     * Cast a vote on a dispute
     */
    public function vote(Request $request, Dispute $dispute): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Validate user can vote
        if (!$dispute->canUserVote($user)) {
            return back()->withErrors(['error' => 'You are not eligible to vote on this dispute.']);
        }

        // Validate vote data
        $validated = $request->validate([
            'vote_outcome' => [
                'required',
                Rule::in(array_column(DisputeVoteOutcome::cases(), 'value')),
            ],
            'selected_outcome' => 'required_if:vote_outcome,different_outcome|nullable|string|max:255',
        ]);

        $voteOutcome = DisputeVoteOutcome::from($validated['vote_outcome']);

        try {
            $this->disputeService->castVote(
                $dispute,
                $user,
                $voteOutcome,
                $validated['selected_outcome'] ?? null
            );

            return redirect()->route('disputes.show', ['dispute' => $dispute->id])
                ->with('success', 'Your vote has been recorded.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to record vote. Please try again.']);
        }
    }
}
