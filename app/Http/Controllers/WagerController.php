<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
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
        // User already authenticated by middleware (if exists)

        // Get or create user from URL parameters
        $user = $this->getOrCreateUser($request);

        // Get or create group from URL parameters (if from a group chat)
        $group = $this->getOrCreateGroup($request);

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
            'type' => 'required|in:binary,multiple_choice,numeric,date',
            'group_id' => 'required|uuid|exists:groups,id',
            'stake_amount' => 'required|integer|min:1',
            'deadline' => 'required|date|after:now',

            // Type-specific fields
            'options' => 'required_if:type,multiple_choice|array|min:2',
            'numeric_min' => 'nullable|integer',
            'numeric_max' => 'nullable|integer|gt:numeric_min',
            'numeric_winner_type' => 'nullable|in:exact,closest',
            'date_min' => 'nullable|date',
            'date_max' => 'nullable|date|after:date_min',
            'date_winner_type' => 'nullable|in:exact,closest',
        ]);

        // User is already authenticated by middleware
        $user = Auth::user();
        $group = Group::findOrFail($validated['group_id']);

        // Create wager
        $wager = $this->wagerService->createWager($group, $user, $validated);
        $wager->load("creator");

        // Post to Telegram group
        $this->postWagerToTelegram($wager, $group);

        return redirect()->route('wager.success', ['wager' => $wager->id]);
    }

    /**
     * Get or create user from platform data (platform-agnostic)
     */
    private function getOrCreateUser(Request $request): User
    {
        // Get platform and user_id from 'u' parameter (already decrypted by middleware)
        $encryptedUserId = $request->query('u');
        $userId = decrypt($encryptedUserId);
        [$platform, $platformUserId] = explode(':', $userId, 2);

        return UserMessengerService::findOrCreate(
            platform: $platform,
            platformUserId: $platformUserId,
            userData: [
                'username' => $request->query('username'),
                'first_name' => $request->query('first_name'),
                'last_name' => $request->query('last_name'),
            ]
        );
    }

    /**
     * Get or create group from platform chat data (platform-agnostic)
     */
    private function getOrCreateGroup(Request $request): Group
    {
        // Get platform from 'u' parameter
        $encryptedUserId = $request->query('u');
        $userId = decrypt($encryptedUserId);
        [$platform, $platformUserId] = explode(':', $userId, 2);

        $chatId = $request->query('chat_id');
        if (!$chatId) {
            return null; // No group context (private chat)
        }

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
        $user = $this->getOrCreateUser($request);
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
     * Post wager announcement to group (platform-agnostic)
     */
    private function postWagerToTelegram($wager, $group): void
    {
        // Platform-agnostic messaging - Group determines which messenger to use
        $message = app(\App\Services\MessageService::class)->wagerAnnouncement($wager);
        $group->sendMessage($message);
        
        /* OLD IMPLEMENTATION (60 lines of hardcoded Telegram HTML):
        $bot = new \TelegramBot\Api\BotApi(config('telegram.bot_token'));
        $deadline = $wager->deadline->setTimezone('Europe/Amsterdam')->format('d M Y H:i');
        $message = "🎲 <b>New Wager Created!</b>\n\n";
        $message .= "<b>Question:</b> {$wager->title}\n";
        ... [56 more lines of hardcoded formatting and keyboard building]
        */
    }

    /**
     * Show success page
     */
    public function success($wagerId)
    {
        $wager = \App\Models\Wager::findOrFail($wagerId);

        return Inertia::render('Wager/Success', [
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'type' => $wager->type,
                'stake_amount' => $wager->stake_amount,
                'deadline' => $wager->deadline->toIso8601String(),
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
        $wager->load(['creator:id,name', 'group:id,name,platform_chat_title', 'entries.user:id,name']);

        // Eager load user balances for all entry users to avoid N+1
        $userIds = $wager->entries->pluck('user_id')->unique();
        $userBalances = \DB::table('group_user')
            ->where('group_id', $wager->group_id)
            ->whereIn('user_id', $userIds)
            ->pluck('points', 'user_id');

        return Inertia::render('Wager/Settle', [
            'token' => $token->token,
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'description' => $wager->description,
                'type' => $wager->type,
                'deadline' => $wager->deadline->toIso8601String(),
                'stake_amount' => $wager->stake_amount,
                'total_points_wagered' => $wager->total_points_wagered,
                'participants_count' => $wager->participants_count,
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
                    'user_balance' => $userBalances[$entry->user_id] ?? 0,
                    'answer_value' => $entry->answer_value,
                    'points_wagered' => $entry->points_wagered,
                ]),
                'options' => $wager->type === 'multiple_choice' ? $wager->options : null,
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
            'outcome_value' => 'required|string',
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

        // Post result to Telegram
        $this->postSettlementToTelegram($settledWager);

        return redirect()->route('wager.settle.success', ['wager' => $settledWager->id]);
    }

    /**
     * Show settlement success page
     */
    public function settlementSuccess($wagerId)
    {
        $wager = \App\Models\Wager::with(['creator:id,name', 'group:id,name,platform_chat_title', 'settler:id,name'])->findOrFail($wagerId);

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
     * Post settlement result to Telegram group
     */
    private function postSettlementToTelegram($wager): void
    {
        $bot = new \TelegramBot\Api\BotApi(config('telegram.bot_token'));

        $message = "🏁 <b>Wager Settled!</b>\n\n";
        $message .= "<b>Question:</b> {$wager->title}\n";
        $message .= "<b>Outcome:</b> {$wager->outcome_value}\n";

        if ($wager->settlement_note) {
            $message .= "<b>Note:</b> {$wager->settlement_note}\n";
        }

        $message .= "\n<b>Winners:</b>\n";

        // Get winning entries
        $winners = $wager->entries()->where('is_winner', true)->with('user')->get();

        if ($winners->count() > 0) {
            foreach ($winners as $winner) {
                $message .= "✅ {$winner->user->name} won {$winner->points_won} points\n";
            }
        } else {
            $message .= "No winners for this wager.\n";
        }

        try {
            $bot->sendMessage(
                $wager->group->platform_chat_id,
                $message,
                'HTML'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to post settlement to Telegram', [
                'wager_id' => $wager->id,
                'error' => $e->getMessage()
            ]);
        }
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

        // Find wager
        $wager = \App\Models\Wager::with(['creator:id,name', 'group:id,name,platform_chat_title', 'entries.user:id,name', 'settler:id,name'])->findOrFail($wagerId);

        $isPastDeadline = $wager->deadline < now();
        $canSettle = $isPastDeadline && $wager->status === 'open';

        // Get user's Telegram username for display
        $telegramService = $user->getTelegramService();

        // Eager load user balances for all entry users to avoid N+1
        $userIds = $wager->entries->pluck('user_id')->unique();
        $userBalances = \DB::table('group_user')
            ->where('group_id', $wager->group_id)
            ->whereIn('user_id', $userIds)
            ->pluck('points', 'user_id');

        return Inertia::render('Wager/Show', [
            'wager' => [
                'id' => $wager->id,
                'title' => $wager->title,
                'description' => $wager->description,
                'type' => $wager->type,
                'deadline' => $wager->deadline->toIso8601String(),
                'stake_amount' => $wager->stake_amount,
                'total_points_wagered' => $wager->total_points_wagered,
                'participants_count' => $wager->participants_count,
                'status' => $wager->status,
                'outcome_value' => $wager->outcome_value,
                'settlement_note' => $wager->settlement_note,
                'settled_at' => $wager->settled_at?->toIso8601String(),
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
                    'user_balance' => $userBalances[$entry->user_id] ?? 0,
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
        ]);
    }

    /**
     * Process settlement from the wager show page
     */
    public function settleFromShow(Request $request, $wagerId)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'outcome_value' => 'required|string',
            'settlement_note' => 'nullable|string|max:500',
        ]);

        // Find wager
        $wager = \App\Models\Wager::findOrFail($wagerId);

        // Verify wager can be settled
        if ($wager->status !== 'open') {
            abort(403, 'Wager is not open for settlement');
        }

        if ($wager->deadline >= now()) {
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

        // Post result to Telegram
        $this->postSettlementToTelegram($settledWager);

        return redirect()->route('wager.settle.success', ['wager' => $settledWager->id]);
    }
}
