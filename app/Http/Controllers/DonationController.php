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
        // Decrypt user identifier
        try {
            $userIdentifier = decrypt($request->input('u'));
            [$platform, $platformUserId] = explode(':', $userIdentifier, 2);
        } catch (\Exception $e) {
            abort(403, 'Invalid or expired link');
        }

        $groupId = $request->input('group_id');

        // Find group
        $group = Group::with('users')->findOrFail($groupId);

        // Find donor user
        $donor = User::where('platform', $platform)
            ->where('platform_user_id', $platformUserId)
            ->firstOrFail();

        // Verify donor is a member
        $donorMembership = $group->users()
            ->where('users.id', $donor->id)
            ->first();

        if (!$donorMembership) {
            abort(403, 'You are not a member of this group');
        }

        // Get eligible recipients (all members except donor)
        $recipients = $group->users()
            ->where('users.id', '!=', $donor->id)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'points' => $user->pivot->points,
            ]);

        return Inertia::render('Donations/Create', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'currency_name' => $group->points_currency_name ?? 'points',
            ],
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->name,
                'points' => $donorMembership->pivot->points,
            ],
            'recipients' => $recipients,
            'encrypted_user' => $request->input('u'),
        ]);
    }

    /**
     * Process the donation
     */
    public function store(Request $request): JsonResponse
    {
        // Decrypt user identifier
        try {
            $userIdentifier = decrypt($request->input('encrypted_user'));
            [$platform, $platformUserId] = explode(':', $userIdentifier, 2);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

        $validated = $request->validate([
            'group_id' => 'required|uuid|exists:groups,id',
            'recipient_id' => 'required|uuid|exists:users,id',
            'amount' => 'required|integer|min:1',
            'is_public' => 'required|boolean',
            'message' => 'nullable|string|max:500',
        ]);

        // Find donor
        $donor = User::where('platform', $platform)
            ->where('platform_user_id', $platformUserId)
            ->firstOrFail();

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

            // Deduct from donor
            $donor->adjustPoints($group, -$amount);

            // Add to recipient
            $recipient->adjustPoints($group, $amount);

            // Create transactions
            Transaction::create([
                'user_id' => $donor->id,
                'group_id' => $group->id,
                'type' => 'donation_sent',
                'amount' => -$amount,
                'description' => "Donated to {$recipient->name}",
                'metadata' => [
                    'recipient_id' => $recipient->id,
                    'is_public' => $validated['is_public'],
                ],
            ]);

            Transaction::create([
                'user_id' => $recipient->id,
                'group_id' => $group->id,
                'type' => 'donation_received',
                'amount' => $amount,
                'description' => "Received from {$donor->name}",
                'metadata' => [
                    'donor_id' => $donor->id,
                    'is_public' => $validated['is_public'],
                ],
            ]);

            // Create audit events
            AuditEvent::create([
                'event_type' => 'donation.sent',
                'group_id' => $group->id,
                'user_id' => $donor->id,
                'metadata' => [
                    'amount' => $amount,
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'is_public' => $validated['is_public'],
                ],
            ]);

            AuditEvent::create([
                'event_type' => 'donation.received',
                'group_id' => $group->id,
                'user_id' => $recipient->id,
                'metadata' => [
                    'amount' => $amount,
                    'donor_id' => $donor->id,
                    'donor_name' => $donor->name,
                    'is_public' => $validated['is_public'],
                ],
            ]);

            // Generate and send message
            $message = $this->generateDonationMessage(
                $group,
                $donor,
                $recipient,
                $amount,
                $validated['is_public'],
                $validated['message'] ?? null
            );

            $group->sendMessage($message);

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
     * Generate donation message (public or private)
     */
    private function generateDonationMessage(
        Group $group,
        User $donor,
        User $recipient,
        int $amount,
        bool $isPublic,
        ?string $userMessage
    ): string {
        $currencyName = $group->points_currency_name ?? 'points';

        if ($isPublic) {
            // Public announcement
            $message = "🎁 Generous Donation!\n\n";
            $message .= "{$donor->name} → {$recipient->name}\n";
            $message .= "Amount: {$amount} {$currencyName}\n";

            if ($userMessage) {
                $message .= "\nMessage: \"{$userMessage}\"\n";
            }

            return $message;
        }

        // Private donation - use LLM for creative message
        try {
            $prompt = "A generous donation of {$amount} {$currencyName} was just made in the group. ";
            $prompt .= "Write a brief, celebratory message (2-3 sentences) announcing this WITHOUT revealing who sent or received it. ";
            $prompt .= "Make it fun and encouraging! Focus on the community spirit.";

            if ($userMessage) {
                $prompt .= " Optional donor message to incorporate naturally: \"{$userMessage}\"";
            }

            return $this->messageService->generateWithLLM($group, $prompt);
        } catch (\Exception $e) {
            Log::error('Failed to generate donation message', [
                'group_id' => $group->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback message
            return "🎁 Someone just made a generous donation of {$amount} {$currencyName}! The kindness is real! 💚";
        }
    }
}
