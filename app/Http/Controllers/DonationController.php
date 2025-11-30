<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    /**
     * Show the donation form
     */
    public function create(Request $request): Response
    {
        // User is already authenticated via signed.auth middleware
        $donor = auth()->user();

        // Get all groups the donor is a member of
        $groups = $donor->groups()->get()->map(function($group) use ($donor) {
            $membership = $group->users()->where('users.id', $donor->id)->first();

            return [
                'id' => $group->id,
                'name' => $group->name,
                'currency_name' => $group->points_currency_name ?? 'points',
                'donor_points' => $membership->pivot->points,
            ];
        });

        if ($groups->isEmpty()) {
            abort(403, 'You are not a member of any groups');
        }

        return Inertia::render('Donations/Create', [
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->name,
            ],
            'groups' => $groups,
        ]);
    }

    /**
     * Get recipients for a specific group
     */
    public function recipients(Request $request, Group $group): JsonResponse
    {
        // User is already authenticated via signed.auth middleware
        $donor = auth()->user();

        // Verify donor is a member
        $donorMembership = $group->users()->where('users.id', $donor->id)->first();
        if (!$donorMembership) {
            return response()->json(['message' => 'Not a member of this group'], 403);
        }

        // Get eligible recipients (all members except donor)
        $recipients = $group->users()
            ->where('users.id', '!=', $donor->id)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'points' => $user->pivot->points,
            ]);

        return response()->json([
            'recipients' => $recipients,
            'donor_points' => $donorMembership->pivot->points,
        ]);
    }

    /**
     * Process the donation
     */
    public function store(Request $request): JsonResponse
    {
        // User is already authenticated via signed.auth middleware
        $donor = auth()->user();

        $validated = $request->validate([
            'group_id' => 'required|uuid|exists:groups,id',
            'recipient_id' => 'required|uuid|exists:users,id',
            'amount' => 'required|integer|min:1',
            'is_public' => 'required|boolean',
            'message' => 'nullable|string|max:500',
        ]);

        // Find group and recipient
        $group = Group::findOrFail($validated['group_id']);
        $recipient = User::findOrFail($validated['recipient_id']);

        // Verify both are members
        $donorMembership = $group->users()->where('users.id', $donor->id)->first();
        $recipientMembership = $group->users()->where('users.id', $recipient->id)->first();

        if (!$donorMembership || !$recipientMembership) {
            return response()->json(['message' => 'Invalid membership'], 403);
        }

        // Check donor has enough points
        if ($donorMembership->pivot->points < $validated['amount']) {
            return response()->json(['message' => 'Insufficient points'], 400);
        }

        // Prevent self-donation
        if ($donor->id === $recipient->id) {
            return response()->json(['message' => 'Cannot donate to yourself'], 400);
        }

        // Process donation in transaction
        DB::transaction(function () use ($donor, $recipient, $group, $validated) {
            $amount = $validated['amount'];

            // Deduct from donor (PointService creates transaction automatically)
            $donor->adjustPoints($group, -$amount, 'donation_sent');

            // Add to recipient (PointService creates transaction automatically)
            $recipient->adjustPoints($group, $amount, 'donation_received');

            // Create audit events
            AuditEvent::create([
                'event_type' => 'donation.sent',
                'group_id' => $group->id,
                'summary' => "Donated {$amount} points to {$recipient->name}",
                'participants' => [
                    [
                        'user_id' => $donor->id,
                        'username' => $donor->name,
                        'role' => 'donor',
                    ],
                    [
                        'user_id' => $recipient->id,
                        'username' => $recipient->name,
                        'role' => 'recipient',
                    ]
                ],
                'impact' => [
                    'points' => -$amount,
                ],
                'metadata' => [
                    'amount' => $amount,
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'is_public' => $validated['is_public'],
                ],
                'created_at' => now(),
            ]);

            AuditEvent::create([
                'event_type' => 'donation.received',
                'group_id' => $group->id,
                'summary' => "Received {$amount} points from {$donor->name}",
                'participants' => [
                    [
                        'user_id' => $donor->id,
                        'username' => $donor->name,
                        'role' => 'donor',
                    ],
                    [
                        'user_id' => $recipient->id,
                        'username' => $recipient->name,
                        'role' => 'recipient',
                    ]
                ],
                'impact' => [
                    'points' => $amount,
                ],
                'metadata' => [
                    'amount' => $amount,
                    'donor_id' => $donor->id,
                    'donor_name' => $donor->name,
                    'is_public' => $validated['is_public'],
                ],
                'created_at' => now(),
            ]);

            // Generate and send messages based on public/silent preference
            $this->sendDonationMessages(
                $group,
                $donor,
                $recipient,
                $amount,
                $validated['is_public'],
                $validated['message'] ?? null
            );

            Log::channel('operational')->info('donation.completed', [
                'group_id' => $group->id,
                'donor_id' => $donor->id,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
                'is_public' => $validated['is_public'],
            ]);
        });

        return response()->json([
            'message' => 'Donation successful!',
            'success' => true,
        ]);
    }

    /**
     * Send donation messages (silent DM-only or public group announcement)
     */
    private function sendDonationMessages(
        Group $group,
        User $donor,
        User $recipient,
        int $amount,
        bool $isPublic,
        ?string $userMessage
    ): void {
        $currencyName = $group->points_currency_name ?? 'points';

        if ($isPublic) {
            // Public announcement in group (via LLM)
            try {
                $prompt = "{$donor->name} just sent {$amount} {$currencyName} to {$recipient->name}! ";
                $prompt .= "Write a brief, celebratory announcement (2-3 sentences) celebrating this generous act. ";
                $prompt .= "Make it warm and fun!";

                if ($userMessage) {
                    $prompt .= " Include their message: \"{$userMessage}\"";
                }

                $generatedContent = $this->messageService->generateWithLLM($group, $prompt);
                $groupMessage = new \App\DTOs\Message(
                    content: $generatedContent,
                    type: \App\DTOs\MessageType::Announcement,
                    currencyName: $currencyName
                );

                try {
                    $group->sendMessage($groupMessage);
                } catch (\Exception $sendError) {
                    Log::warning('Failed to send donation message', [
                        'group_id' => $group->id,
                        'error' => $sendError->getMessage(),
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Failed to generate public donation message', [
                    'group_id' => $group->id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback public message
                $fallbackText = "🎁 {$donor->name} just sent {$amount} {$currencyName} to {$recipient->name}!";
                if ($userMessage) {
                    $fallbackText .= "\n\nMessage: \"{$userMessage}\"";
                }
                $fallback = new \App\DTOs\Message(
                    content: $fallbackText,
                    type: \App\DTOs\MessageType::Announcement,
                    currencyName: $currencyName
                );

                try {
                    $group->sendMessage($fallback);
                } catch (\Exception $sendError) {
                    Log::warning('Failed to send fallback donation message', [
                        'group_id' => $group->id,
                        'error' => $sendError->getMessage(),
                    ]);
                }
            }
        } else {
            // Silent - DM only to recipient (via LLM)
            try {
                $prompt = "{$donor->name} just sent you {$amount} {$currencyName}! ";
                $prompt .= "Write a brief, warm message (2-3 sentences) letting them know about this gift. ";
                $prompt .= "Make it personal and kind!";

                if ($userMessage) {
                    $prompt .= " Include their message: \"{$userMessage}\"";
                }

                $dmContent = $this->messageService->generateWithLLM($group, $prompt);

                // Send DM to recipient via messenger
                if ($recipient->platform_user_id) {
                    try {
                        app(\App\Messaging\MessengerAdapterInterface::class)->sendDirectMessage(
                            $recipient->platform_user_id,
                            \App\Messaging\DTOs\OutgoingMessage::text($recipient->platform_user_id, $dmContent)
                        );
                    } catch (\Exception $sendError) {
                        Log::warning('Failed to send donation DM', [
                            'group_id' => $group->id,
                            'recipient_id' => $recipient->id,
                            'error' => $sendError->getMessage(),
                        ]);
                    }
                }

            } catch (\Exception $e) {
                Log::error('Failed to generate silent donation DM', [
                    'group_id' => $group->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback DM
                $fallback = "🎁 {$donor->name} just sent you {$amount} {$currencyName}!";
                if ($userMessage) {
                    $fallback .= "\n\nMessage: \"{$userMessage}\"";
                }

                if ($recipient->platform_user_id) {
                    try {
                        app(\App\Messaging\MessengerAdapterInterface::class)->sendDirectMessage(
                            $recipient->platform_user_id,
                            \App\Messaging\DTOs\OutgoingMessage::text($recipient->platform_user_id, $fallback)
                        );
                    } catch (\Exception $sendError) {
                        Log::warning('Failed to send fallback donation DM', [
                            'group_id' => $group->id,
                            'recipient_id' => $recipient->id,
                            'error' => $sendError->getMessage(),
                        ]);
                    }
                }
            }
        }
    }
}
