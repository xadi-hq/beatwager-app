<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Services\UserMessengerService;
use App\Services\WagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WagerController extends Controller
{
    public function __construct(
        private readonly WagerService $wagerService
    ) {}

    /**
     * Show the wager creation form
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
                'telegram_chat_title' => $g->platform_chat_title, // Legacy field name for frontend compatibility
            ]);

        // Get Telegram username for display
        $telegramService = $user->getTelegramService();

        // If no groups available, set default to null
        // For Telegram: only real groups have negative chat IDs
        $defaultGroup = $group && ($group->platform !== 'telegram' || (int)$group->platform_chat_id < 0) ? [
            'id' => $group->id,
            'name' => $group->platform_chat_title ?: $group->name,
        ] : null;

        return Inertia::render('Wager/Create', [
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
     * Store a new wager
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resolution_criteria' => 'nullable|string',
            'type' => 'required|in:binary_yes_no,binary_over_under,binary_before_after,binary_custom,multiple_choice,numeric,date,short_answer,top_n_ranking',
            'group_id' => 'required|uuid|exists:groups,id',
            'stake_amount' => 'required|integer|min:1',
            'betting_closes_at' => 'required|date|after:now',
            'expected_settlement_at' => 'nullable|date|after:betting_closes_at',

            // Binary flexible labels
            'label_option_a' => 'required_if:type,binary_custom|nullable|string|max:50',
            'label_option_b' => 'required_if:type,binary_custom|nullable|string|max:50',
            'threshold_value' => 'required_if:type,binary_over_under|nullable|numeric',
            'threshold_date' => 'required_if:type,binary_before_after|nullable|date',

            // Type-specific fields
            'options' => 'required_if:type,multiple_choice,top_n_ranking|array|min:2',
            'numeric_min' => 'nullable|integer',
            'numeric_max' => 'nullable|integer|gt:numeric_min',
            'numeric_winner_type' => 'nullable|in:exact,closest',
            'date_min' => 'nullable|date',
            'date_max' => 'nullable|date|after:date_min',
            'date_winner_type' => 'nullable|in:exact,closest',

            // Complex type fields
            'max_length' => 'nullable|integer|min:10|max:500',
            'n' => 'required_if:type,top_n_ranking|integer|min:2',
        ]);

        // Normalize binary subtypes to 'binary' for storage
        if (in_array($validated['type'], ['binary_yes_no', 'binary_over_under', 'binary_before_after', 'binary_custom'])) {
            $validated['type'] = 'binary';
        }

        // User is already authenticated by middleware
        $user = Auth::user();
        $group = Group::findOrFail($validated['group_id']);

        // Convert datetime inputs from group timezone to UTC for storage
        $validated['betting_closes_at'] = $group->toUtc($validated['betting_closes_at']);
        if (!empty($validated['expected_settlement_at'])) {
            $validated['expected_settlement_at'] = $group->toUtc($validated['expected_settlement_at']);
        }

        // Create wager
        $wager = $this->wagerService->createWager($group, $user, $validated);
        $wager->load("creator");

        // Dispatch event for async announcement (non-blocking)
        \App\Events\WagerCreated::dispatch($wager);

        return redirect()->route('wager.success', ['wager' => $wager->id]);
    }

    /**
     * Get or create group from platform chat data (platform-agnostic)
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
     * Show success page
     */
    public function success($wagerId)
    {
        $wager = Wager::findOrFail($wagerId);

        return Inertia::render('Wager/Success', [
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'type' => $wager->type,
                'stake_amount' => $wager->stake_amount,
                'betting_closes_at' => $wager->betting_closes_at->toIso8601String(),
                'expected_settlement_at' => $wager->expected_settlement_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Show settlement form
     */
    public function showSettlementForm(Request $request)
    {
        $validated = $request->validate(['token' => 'required|string']);

        $token = \App\Models\WagerSettlementToken::where('token', $validated['token'])->first();

        if (!$token || !$token->isValid()) {
            abort(403, 'Invalid or expired settlement token');
        }

        $wager = $token->wager;
        $wager->load([
            'creator:id,name',
            'group:id,name,platform_chat_title,points_currency_name',
            'entries.user:id,name',
            'entries.user.groups' => function ($query) use ($wager) {
                $query->where('groups.id', $wager->group_id)
                    ->withPivot('points');
            }
        ]);

        return Inertia::render('Wager/Settle', [
            'token' => $token->token,
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'description' => $wager->description,
                'type' => $wager->type,
                'betting_closes_at' => $wager->betting_closes_at->toIso8601String(),
                'expected_settlement_at' => $wager->expected_settlement_at?->toIso8601String(),
                'stake_amount' => $wager->stake_amount,
                'total_points_wagered' => $wager->total_points_wagered,
                'participants_count' => $wager->participants_count,
                'currency' => $wager->group->points_currency_name ?? 'points',
                'creator' => [
                    'id' => $wager->creator->id,
                    'name' => $wager->creator->name,
                ],
                'group' => [
                    'id' => $wager->group->id,
                    'name' => $wager->group->platform_chat_title ?? $wager->group->name,
                ],
                'entries' => $wager->entries->map(fn($entry) => [
                    'id' => $entry->id,
                    'user_name' => $entry->user->name,
                    'user_balance' => $entry->user->groups->first()?->pivot->points ?? 0,
                    'answer_value' => $entry->answer_value,
                    'points_wagered' => $entry->points_wagered,
                ]),
                'options' => $wager->type === 'multiple_choice' ? $wager->options : null,
                'type_config' => match ($wager->type) {
                    'short_answer' => ['max_length' => $wager->max_length],
                    'top_n_ranking' => ['options' => $wager->options, 'n' => $wager->n],
                    default => null,
                },
            ],
        ]);
    }

    /**
     * Process settlement
     */
    public function settle(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'outcome_value' => 'present', // Can be string or array (including empty array) depending on wager type
            'settlement_note' => 'nullable|string|max:500',
        ]);

        $token = \App\Models\WagerSettlementToken::where('token', $validated['token'])->first();

        if (!$token || !$token->isValid()) {
            abort(403, 'Invalid or expired settlement token');
        }

        $wager = $token->wager;

        // Settle the wager
        $settledWager = $this->wagerService->settleWager(
            $wager,
            $validated['outcome_value'],
            $validated['settlement_note'] ?? null
        );

        // Mark token as used
        $token->markAsUsed();

        // Dispatch async settlement announcement
        \App\Events\WagerSettled::dispatch($settledWager);

        return redirect()->route('wager.settle.success', ['wager' => $settledWager->id]);
    }

    /**
     * Show settlement success page
     */
    public function settlementSuccess($wagerId)
    {
        $wager = Wager::with(['creator:id,name', 'group:id,name,platform_chat_title', 'settler:id,name'])->findOrFail($wagerId);

        return Inertia::render('Wager/SettlementSuccess', [
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'outcome_value' => $wager->outcome_value,
                'settlement_note' => $wager->settlement_note,
                'settled_at' => $wager->settled_at?->toIso8601String(),
                'settler' => $wager->settler ? [
                    'id' => $wager->settler->id,
                    'name' => $wager->settler->name,
                ] : null,
                'settled_at' => $wager->settled_at->toIso8601String(),
                'group' => [
                    'name' => $wager->group->platform_chat_title ?? $wager->group->name,
                ],
            ],
        ]);
    }

    /**
     * Show wager landing page with progress, stats, and conditional settlement
     *
     * Authentication handled by 'signed.auth' middleware - user is already authenticated via session
     */
    public function show(Request $request, $wagerId)
    {
        // Get authenticated user from session (middleware handles auth)
        $user = Auth::user();

        // Find wager with optimized eager loading including user balances
        $wager = Wager::with([
            'creator:id,name',
            'group:id,name,platform_chat_title,points_currency_name',
            'entries.user:id,name',
            'settler:id,name'
        ])->findOrFail($wagerId);

        // Eager load user balances for entry users
        $wager->load(['entries.user.groups' => function ($query) use ($wager) {
            $query->where('groups.id', $wager->group_id)
                ->withPivot('points');
        }]);

        $isPastDeadline = $wager->isPastBettingDeadline();
        $canSettle = $isPastDeadline && $wager->status === 'open';

        // Get user's Telegram username for display
        $telegramService = $user->getTelegramService();

        return Inertia::render('Wager/Show', [
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'description' => $wager->description,
                'type' => $wager->type,
                'betting_closes_at' => $wager->betting_closes_at->toIso8601String(),
                'expected_settlement_at' => $wager->expected_settlement_at?->toIso8601String(),
                'stake_amount' => $wager->stake_amount,
                'total_points_wagered' => $wager->total_points_wagered,
                'participants_count' => $wager->participants_count,
                'status' => $wager->status,
                'outcome_value' => $wager->outcome_value,
                'settlement_note' => $wager->settlement_note,
                'settled_at' => $wager->settled_at?->toIso8601String(),
                'currency' => $wager->group->points_currency_name ?? 'points',
                'settler' => $wager->settler ? [
                    'id' => $wager->settler->id,
                    'name' => $wager->settler->name,
                ] : null,
                'creator' => [
                    'id' => $wager->creator->id,
                    'name' => $wager->creator->name,
                ],
                'group' => [
                    'id' => $wager->group->id,
                    'name' => $wager->group->platform_chat_title ?? $wager->group->name,
                ],
                'entries' => $wager->entries->map(fn($entry) => [
                    'id' => $entry->id,
                    'user_name' => $entry->user->name,
                    'user_balance' => $entry->user->groups->first()?->pivot->points ?? 0,
                    'answer_value' => $isPastDeadline ? $entry->answer_value : null,
                    'points_wagered' => $entry->points_wagered,
                    'is_winner' => $entry->is_winner,
                    'points_won' => $entry->points_won,
                ]),
                'options' => $wager->type === 'multiple_choice' ? $wager->options : null,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'telegram_username' => $telegramService?->username,
            ],
            'canSettle' => $canSettle,
            'isPastDeadline' => $isPastDeadline,
            'type_config' => $wager->type_config,
        ]);
    }

    /**
     * Process settlement from the wager show page
     */
    public function settleFromShow(Request $request, $wagerId)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'outcome_value' => 'present', // Can be string or array (including empty array) depending on wager type
            'settlement_note' => 'nullable|string|max:500',
        ]);

        // Find wager
        $wager = Wager::findOrFail($wagerId);

        // Verify wager can be settled
        if ($wager->status !== 'open') {
            abort(403, 'Wager is not open for settlement');
        }

        // Allow settlement after deadline has passed
        if ($wager->isBettingOpen()) {
            abort(403, 'Cannot settle wager before deadline');
        }

        // Find settler by user ID (already authenticated via session)
        $settler = \App\Models\User::findOrFail($validated['user_id']);

        // Settle the wager with settler_id
        $settledWager = $this->wagerService->settleWager(
            $wager,
            $validated['outcome_value'],
            $validated['settlement_note'] ?? null,
            $settler->id
        );

        // Dispatch async settlement announcement
        \App\Events\WagerSettled::dispatch($settledWager);

        return redirect()->route('wager.settle.success', ['wager' => $settledWager->id]);
    }

    /**
     * Show wager join form (for complex input types)
     * User authenticated via signed.auth middleware (consistent with wager.create)
     */
    public function showJoinForm(Request $request, Wager $wager)
    {
        // User already authenticated by signed.auth middleware
        $user = Auth::user();

        // Load relationships
        $wager->load(['group', 'creator']);

        // Check if betting is still open
        if (!$wager->isBettingOpen()) {
            return Inertia::render('Wager/JoinClosed', [
                'wager' => [
                    'id' => $wager->id,
                    'title' => $wager->title,
                    'betting_closes_at' => $wager->betting_closes_at,
                    'status' => $wager->status,
                    'group' => [
                        'name' => $wager->group->platform_chat_title ?? $wager->group->name,
                    ],
                ],
            ]);
        }

        // Check if user already joined
        if ($wager->entries()->where('user_id', $user->id)->exists()) {
            return redirect()->route('wager.show', ['wager' => $wager->id]);
        }

        // Get user's balance in this group
        $balance = \Illuminate\Support\Facades\DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $wager->group_id)
            ->value('points') ?? 0;

        // Check sufficient balance
        if ($balance < $wager->stake_amount) {
            return Inertia::render('Wager/InsufficientBalance', [
                'wager' => [
                    'id' => $wager->id,
                    'title' => $wager->title,
                    'stake_amount' => $wager->stake_amount,
                    'group' => [
                        'name' => $wager->group->platform_chat_title ?? $wager->group->name,
                    ],
                ],
                'user' => [
                    'name' => $user->name,
                    'balance' => $balance,
                ],
            ]);
        }

        return Inertia::render('Wager/Join', [
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'description' => $wager->description,
                'type' => $wager->type,
                'type_config' => $wager->getTypeConfig(),
                'stake_amount' => $wager->stake_amount,
                'betting_closes_at' => $wager->betting_closes_at->toIso8601String(),
                'group' => [
                    'id' => $wager->group->id,
                    'name' => $wager->group->platform_chat_title ?? $wager->group->name,
                ],
                'creator' => [
                    'name' => $wager->creator->name,
                ],
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'balance' => $balance,
            ],
        ]);
    }

    /**
     * Submit wager entry from join form
     * User authenticated via signed.auth middleware (consistent with wager.store)
     */
    public function submitJoin(Request $request, Wager $wager)
    {
        // User already authenticated by signed.auth middleware
        $user = Auth::user();

        // Basic validation
        $validated = $request->validate([
            'answer_value' => 'required',
        ]);

        try {
            // Place wager entry (WagerService handles all validation)
            $entry = $this->wagerService->placeWager(
                $wager,
                $user,
                $validated['answer_value'],
                $wager->stake_amount
            );

            // Dispatch join event (async announcement)
            \App\Events\WagerJoined::dispatch($wager, $entry, $user);

            return redirect()->route('wager.join.success', ['entry' => $entry->id]);

        } catch (\App\Exceptions\InvalidAnswerException $e) {
            return back()->withErrors(['answer_value' => $e->getMessage()]);
        } catch (\App\Exceptions\UserAlreadyJoinedException $e) {
            return redirect()->route('wager.show', ['wager' => $wager->id])
                ->with('message', 'You have already joined this wager.');
        } catch (\App\Exceptions\InvalidStakeException $e) {
            return back()->withErrors(['stake' => $e->getMessage()]);
        } catch (\App\Exceptions\WagerNotOpenException $e) {
            return back()->withErrors(['wager' => 'This wager is no longer open for entries.']);
        }
    }

    /**
     * Show join success page
     */
    public function joinSuccess($entryId)
    {
        $entry = \App\Models\WagerEntry::with(['wager.group', 'user'])
            ->findOrFail($entryId);

        // Get user's updated balance in this group
        $balance = \Illuminate\Support\Facades\DB::table('group_user')
            ->where('user_id', $entry->user_id)
            ->where('group_id', $entry->wager->group_id)
            ->value('points') ?? 0;

        return Inertia::render('Wager/JoinSuccess', [
            'entry' => [
                'id' => $entry->id,
                'answer_value' => $entry->answer_value,
                'stake_amount' => $entry->points_wagered,
                'created_at' => $entry->created_at->toIso8601String(),
            ],
            'wager' => [
                'id' => $entry->wager->id,
                'title' => $entry->wager->title,
                'type' => $entry->wager->type,
                'betting_closes_at' => $entry->wager->betting_closes_at->toIso8601String(),
                'currency' => $entry->wager->group->points_currency_name ?? 'points',
                'group' => [
                    'name' => $entry->wager->group->platform_chat_title ?? $entry->wager->group->name,
                ],
            ],
            'user' => [
                'name' => $entry->user->name,
                'balance' => $balance,
            ],
        ]);
    }
}
