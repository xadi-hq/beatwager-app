<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\SuperChallengeNudge;
use App\Services\SuperChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SuperChallengeController extends Controller
{
    public function __construct(
        private readonly SuperChallengeService $superChallengeService
    ) {}

    /**
     * Respond to nudge (accept or decline)
     * Route: /superchallenge/nudge/{nudge}/respond?action=accept|decline
     */
    public function respondToNudge(Request $request, SuperChallengeNudge $nudge): Response|RedirectResponse
    {
        $user = Auth::user();

        // Validate this is the nudged user
        if ($nudge->nudged_user_id !== $user->id) {
            abort(403, 'This nudge was not sent to you');
        }

        if (!$nudge->isPending()) {
            abort(400, 'This nudge has already been responded to');
        }

        $action = $request->query('action');

        if ($action === 'decline') {
            $nudge->update([
                'response' => \App\Enums\NudgeResponse::DECLINED,
                'responded_at' => now(),
            ]);

            return Inertia::render('SuperChallenge/Declined', [
                'group' => $nudge->group->only(['id', 'name', 'platform_chat_title']),
            ]);
        }

        // Action is accept - show creation form
        return $this->showCreateForm($nudge);
    }

    /**
     * Show SuperChallenge creation form (after nudge accepted)
     */
    private function showCreateForm(SuperChallengeNudge $nudge): Response
    {
        $user = Auth::user();
        $group = $nudge->group;

        // Calculate prize structure and ranges for sliders
        [$prizeRange, $participantRange, $creatorRewards] = $this->calculatePrizePreview($group);

        return Inertia::render('SuperChallenge/Create', [
            'user' => $user->only(['id', 'name']),
            'group' => [
                'id' => $group->id,
                'name' => $group->platform_chat_title ?: $group->name,
                'member_count' => $group->users()->count(),
            ],
            'nudge_id' => $nudge->id,
            'prize_range' => $prizeRange,
            'participant_range' => $participantRange,
            'creator_rewards' => $creatorRewards,
            'static_examples' => $this->getStaticExamples(),
            'currencyName' => $group->points_currency_name ?? 'points',
        ]);
    }

    /**
     * Create SuperChallenge from nudged user's input
     */
    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nudge_id' => 'required|uuid|exists:super_challenge_nudges,id',
            'description' => 'required|string|max:200',
            'deadline_days' => 'required|integer|in:7,14,30,60',
            'prize_per_person' => 'required|integer|min:50|max:150',
            'max_participants' => 'required|integer|min:1|max:10',
            'evidence_guidance' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $nudge = SuperChallengeNudge::findOrFail($validated['nudge_id']);

        // Validate
        if ($nudge->nudged_user_id !== $user->id) {
            abort(403);
        }

        if (!$nudge->isPending()) {
            abort(400, 'This nudge has already been responded to');
        }

        // Additional validation: max_participants can't exceed group size - 1
        $group = $nudge->group;
        $maxAllowed = min(10, $group->users()->count() - 1);
        if ($validated['max_participants'] > $maxAllowed) {
            return back()->withErrors(['max_participants' => "Maximum {$maxAllowed} participants allowed for this group."]);
        }

        $challenge = $this->superChallengeService->createSuperChallenge(
            $nudge,
            $validated['description'],
            $validated['deadline_days'],
            $validated['prize_per_person'],
            $validated['max_participants'],
            $validated['evidence_guidance'] ?? null
        );

        return redirect()->route('superchallenge.created', ['challenge' => $challenge->id]);
    }

    /**
     * Show success page after creation
     */
    public function created(Challenge $challenge): Response
    {
        if (!$challenge->isSuperChallenge()) {
            abort(404);
        }

        return Inertia::render('SuperChallenge/Created', [
            'challenge' => [
                'id' => $challenge->id,
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'max_participants' => $challenge->max_participants,
                'deadline' => $challenge->group->toGroupTimezone($challenge->completion_deadline)->format('M j, Y'),
            ],
            'group' => [
                'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
            ],
        ]);
    }

    /**
     * Accept SuperChallenge (participant joins)
     * Route: /superchallenge/{challenge}/accept
     */
    public function accept(Request $request, Challenge $challenge): Response|RedirectResponse
    {
        $user = Auth::user();

        if (!$challenge->isSuperChallenge()) {
            abort(404);
        }

        // Show acceptance confirmation page first
        if (!$request->has('confirmed')) {
            return Inertia::render('SuperChallenge/Accept', [
                'challenge' => [
                    'id' => $challenge->id,
                    'description' => $challenge->description,
                    'prize_per_person' => $challenge->prize_per_person,
                    'max_participants' => $challenge->max_participants,
                    'current_participants' => $challenge->participants()->count(),
                    'deadline' => $challenge->group->toGroupTimezone($challenge->completion_deadline)->format('M j, Y g:i A'),
                    'evidence_guidance' => $challenge->evidence_guidance,
                ],
                'group' => [
                    'name' => $challenge->group->platform_chat_title ?: $challenge->group->name,
                ],
                'user' => $user->only(['id', 'name']),
            ]);
        }

        // User confirmed - accept the challenge
        try {
            $this->superChallengeService->acceptChallenge($challenge, $user);

            return Inertia::location(route('superchallenge.accepted', ['challenge' => $challenge->id]));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show acceptance success page
     */
    public function accepted(Challenge $challenge): Response
    {
        if (!$challenge->isSuperChallenge()) {
            abort(404);
        }

        return Inertia::render('SuperChallenge/Accepted', [
            'challenge' => [
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'deadline' => $challenge->group->toGroupTimezone($challenge->completion_deadline)->format('M j, Y'),
            ],
        ]);
    }

    /**
     * Claim completion
     * Route: /superchallenge/{challenge}/complete
     */
    public function complete(Request $request, Challenge $challenge): Response|RedirectResponse
    {
        $user = Auth::user();

        if (!$challenge->isSuperChallenge()) {
            abort(404);
        }

        // Show completion form
        if (!$request->isMethod('post')) {
            return Inertia::render('SuperChallenge/Complete', [
                'challenge' => [
                    'id' => $challenge->id,
                    'description' => $challenge->description,
                    'evidence_guidance' => $challenge->evidence_guidance,
                ],
            ]);
        }

        // Process completion claim
        try {
            $this->superChallengeService->claimCompletion($challenge, $user);

            return Inertia::location(route('superchallenge.completed', ['challenge' => $challenge->id]));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show completion success page
     */
    public function completed(Challenge $challenge): Response
    {
        if (!$challenge->isSuperChallenge()) {
            abort(404);
        }

        return Inertia::render('SuperChallenge/Completed', [
            'challenge' => [
                'description' => $challenge->description,
            ],
            'message' => 'Your completion has been submitted! The creator will validate it soon.',
        ]);
    }

    /**
     * Validate completion (creator approves/rejects)
     * Route: /superchallenge/participant/{participant}/validate?vote=approve|reject
     */
    public function validate(Request $request, ChallengeParticipant $participant): Response|RedirectResponse
    {
        $user = Auth::user();
        $challenge = $participant->challenge;

        // Validate creator
        if ($challenge->creator_id !== $user->id) {
            abort(403, 'Only the creator can validate completions');
        }

        $vote = $request->query('vote');

        if (!in_array($vote, ['approve', 'reject'])) {
            abort(400, 'Invalid vote');
        }

        // Show validation page with participant details
        if (!$request->has('confirmed')) {
            return Inertia::render('SuperChallenge/Validate', [
                'participant' => [
                    'id' => $participant->id,
                    'user_name' => $participant->user->name,
                    'completed_at' => $challenge->group->toGroupTimezone($participant->completed_at)->format('M j, Y g:i A'),
                ],
                'challenge' => [
                    'description' => $challenge->description,
                    'prize_per_person' => $challenge->prize_per_person,
                ],
                'vote' => $vote,
            ]);
        }

        // User confirmed - validate
        try {
            $this->superChallengeService->validateCompletion(
                $participant,
                $user,
                $vote === 'approve'
            );

            return Inertia::location(route('superchallenge.validated', [
                'participant' => $participant->id,
                'result' => $vote,
            ]));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show validation success page
     */
    public function validated(Request $request, ChallengeParticipant $participant): Response
    {
        $result = $request->query('result');

        return Inertia::render('SuperChallenge/Validated', [
            'participant' => [
                'user_name' => $participant->user->name,
            ],
            'approved' => $result === 'approve',
            'prize' => $participant->challenge->prize_per_person,
        ]);
    }

    /**
     * Calculate prize preview and ranges for sliders
     */
    private function calculatePrizePreview($group): array
    {
        $totalGroupPoints = $group->users()->sum('group_user.points');
        $percentage = rand(2, 5);
        $rawPrize = ($totalGroupPoints * $percentage) / 100;
        $suggestedPrize = round($rawPrize / 50) * 50;
        $suggestedPrize = max(50, min(150, (int) $suggestedPrize));

        // Prize range: 50-150 points per person (in steps of 10)
        $prizeRange = [
            'min' => 50,
            'max' => 150,
            'step' => 10,
            'suggested' => $suggestedPrize,
        ];

        // Max participants: 1 to min(groupSize - 1, 10)
        $groupSize = $group->users()->count();
        $maxPossibleParticipants = min(10, $groupSize - 1);

        $participantRange = [
            'min' => 1,
            'max' => $maxPossibleParticipants,
            'suggested' => min(5, $maxPossibleParticipants), // Suggest ~half or 5
        ];

        // Creator reward structure with dynamic bonus based on prize selection
        // Formula: creatorBonus = (maxPrize - selectedPrize) + baseBonus
        // This incentivizes creators to balance attracting participants vs their own reward
        $creatorRewards = [
            'base_acceptance_bonus' => 50,  // When ≥1 participant accepts
            'per_validation_bonus' => 25,   // Per completion validated
            'dynamic_bonus_formula' => [
                'description' => 'Your bonus = (150 - selected_prize) + 50',
                'examples' => [
                    ['prize' => 50, 'bonus' => 150],  // (150-50)+50 = 150
                    ['prize' => 100, 'bonus' => 100], // (150-100)+50 = 100
                    ['prize' => 150, 'bonus' => 50],  // (150-150)+50 = 50
                ],
            ],
        ];

        return [$prizeRange, $participantRange, $creatorRewards];
    }

    /**
     * Get static challenge examples for creation form
     */
    private function getStaticExamples(): array
    {
        return [
            [
                'category' => 'Fitness & Wellness',
                'icon' => '🏃',
                'examples' => [
                    'Run 5km in under 30 minutes',
                    '10,000 steps daily for 7 consecutive days',
                    'No alcohol for 7 consecutive days',
                    'Meditate for 10 minutes daily for 7 consecutive days',
                ],
            ],
            [
                'category' => 'Learning & Productivity',
                'icon' => '📚',
                'examples' => [
                    'Read a non-fiction book',
                    'Complete an online course or tutorial',
                    'Write a blog post or journal entry daily for 7 consecutive days',
                ],
            ],
            [
                'category' => 'Creative',
                'icon' => '🎨',
                'examples' => [
                    'Take a daily photo with different themes for 7 consecutive days',
                    'Create something with your hands (art, craft, woodwork)',
                    'Write a handwritten short story or poem',
                ],
            ],
            [
                'category' => 'Social',
                'icon' => '👥',
                'examples' => [
                    'Cook dinner for the group',
                    'Organize a game night',
                    'Try a new restaurant and share photos',
                ],
            ],
            [
                'category' => 'Gaming',
                'icon' => '🎮',
                'examples' => [
                    'Beat your high score in a game',
                    'Complete a challenging achievement',
                    'Win a board game tournament',
                ],
            ],
        ];
    }
}
