<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Group;
use App\Models\SuperChallengeNudge;
use App\Models\User;
use App\Services\SuperChallengeService;
use Illuminate\Console\Command;

class TestSuperChallenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:superchallenge
                            {action : Action to perform: nudge, process-eligible, auto-approve, list}
                            {--group= : Group ID for nudge action}
                            {--user= : User ID for nudge action (optional, random if not provided)}
                            {--challenge= : Challenge ID for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SuperChallenge system (trigger nudges, process eligibility, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(SuperChallengeService $service)
    {
        $action = $this->argument('action');

        return match ($action) {
            'nudge' => $this->testNudge($service),
            'process-eligible' => $this->testProcessEligible($service),
            'auto-approve' => $this->testAutoApprove($service),
            'list' => $this->listActiveChallenges(),
            default => $this->error("Unknown action: {$action}. Use: nudge, process-eligible, auto-approve, or list"),
        };
    }

    /**
     * Test sending a nudge to a specific user in a group
     */
    private function testNudge(SuperChallengeService $service): int
    {
        $this->info('🎯 Testing SuperChallenge Nudge...');
        $this->newLine();

        $groupId = $this->option('group');
        if (!$groupId) {
            $this->error('Please provide --group=<id>');
            return Command::FAILURE;
        }

        $group = Group::find($groupId);
        if (!$group) {
            $this->error("Group {$groupId} not found");
            return Command::FAILURE;
        }

        $this->info("Group: {$group->name}");
        $this->info("Frequency: " . ($group->superchallenge_frequency ?? 'monthly'));
        $this->info("Last SuperChallenge: " . ($group->last_superchallenge_at?->format('M j, Y g:i A') ?? 'Never'));
        $this->newLine();

        // Get or select user
        $userId = $this->option('user');
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User {$userId} not found");
                return Command::FAILURE;
            }

            // Check if user is in the group
            if (!$group->users->contains($user->id)) {
                $this->error("{$user->name} is not a member of {$group->name}");
                return Command::FAILURE;
            }
        } else {
            // Pick a random user from the group
            $eligibleUsers = $group->users()->get();

            if ($eligibleUsers->isEmpty()) {
                $this->error('No eligible users in this group');
                return Command::FAILURE;
            }

            $user = $eligibleUsers->random();
            $this->info("Randomly selected: {$user->name}");
        }

        $this->newLine();
        $this->info("Creating nudge for {$user->name}...");

        try {
            // Create the nudge directly
            $nudge = SuperChallengeNudge::create([
                'group_id' => $group->id,
                'nudged_user_id' => $user->id,
                'response' => \App\Enums\NudgeResponse::PENDING->value,
                'sent_at' => now(),
            ]);

            // Fire the event to send the message
            event(new \App\Events\SuperChallengeNudgeSent($nudge));

            $this->info('✅ Nudge created and event dispatched!');
            $this->newLine();

            $this->table(
                ['Field', 'Value'],
                [
                    ['Nudge ID', $nudge->id],
                    ['Group', $group->name],
                    ['Nudged User', $user->name],
                    ['Status', $nudge->response->value],
                    ['Created', $nudge->created_at->format('M j, Y g:i A')],
                ]
            );

            $this->newLine();
            $this->info('📱 Check Telegram DM for the nudge message!');
            $this->info("💡 User can respond via: /superchallenge/nudge/{$nudge->id}/respond");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create nudge: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Test the eligibility processing (daily cron job)
     */
    private function testProcessEligible(SuperChallengeService $service): int
    {
        $this->info('🔄 Testing SuperChallenge Eligibility Processing...');
        $this->newLine();

        try {
            $this->info('Running processEligibleGroups()...');
            $service->processEligibleGroups();

            $this->info('✅ Processing completed!');
            $this->newLine();

            // Show any nudges that were created
            $recentNudges = SuperChallengeNudge::where('sent_at', '>=', now()->subMinutes(5))
                ->with(['group', 'nudgedUser'])
                ->get();

            if ($recentNudges->isEmpty()) {
                $this->warn('No nudges were created. Either:');
                $this->warn('  - No groups are eligible (check frequency and last_superchallenge_at)');
                $this->warn('  - All groups have <3 members');
                $this->warn('  - Groups recently had a SuperChallenge');
            } else {
                $this->info("Created {$recentNudges->count()} nudge(s):");
                $this->newLine();

                foreach ($recentNudges as $nudge) {
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['Nudge ID', $nudge->id],
                            ['Group', $nudge->group->name],
                            ['Nudged User', $nudge->nudgedUser->name],
                            ['Status', $nudge->response->value],
                            ['Sent At', $nudge->sent_at->format('M j, Y g:i A')],
                        ]
                    );
                    $this->newLine();
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process eligibility: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Test the auto-approval process (48h timeout)
     */
    private function testAutoApprove(SuperChallengeService $service): int
    {
        $this->info('⏰ Testing SuperChallenge Auto-Approval...');
        $this->newLine();

        // Find pending completions
        $pendingCompletions = ChallengeParticipant::where('validation_status', \App\Enums\ValidationStatus::PENDING)
            ->whereNotNull('completed_at')
            ->with(['challenge', 'user'])
            ->get();

        if ($pendingCompletions->isEmpty()) {
            $this->warn('No pending completions found to auto-approve.');
            $this->warn('To test this:');
            $this->warn('  1. Create a SuperChallenge');
            $this->warn('  2. Have someone accept and claim completion');
            $this->warn('  3. Wait 48 hours (or manually update completed_at to 49 hours ago)');
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingCompletions->count()} pending completion(s):");
        $this->newLine();

        foreach ($pendingCompletions as $completion) {
            $hoursAgo = $completion->completed_at->diffInHours(now());
            $eligible = $hoursAgo >= 48;

            $this->table(
                ['Field', 'Value'],
                [
                    ['Participant', $completion->user->name],
                    ['Challenge', $completion->challenge->description],
                    ['Completed At', $completion->completed_at->format('M j, Y g:i A')],
                    ['Hours Ago', $hoursAgo],
                    ['Auto-Approve Eligible', $eligible ? '✅ Yes' : '❌ No (< 48h)'],
                ]
            );
            $this->newLine();
        }

        try {
            $this->info('Running processAutoApprovals()...');
            $service->processAutoApprovals();

            $this->info('✅ Auto-approval processing completed!');
            $this->info('Check logs for details on any auto-approved completions.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process auto-approvals: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * List all active SuperChallenges
     */
    private function listActiveChallenges(): int
    {
        $this->info('📋 Active SuperChallenges');
        $this->newLine();

        $challenges = Challenge::where('type', \App\Enums\ChallengeType::SUPER_CHALLENGE)
            ->where('status', 'active')
            ->with(['group', 'creator', 'participants.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($challenges->isEmpty()) {
            $this->warn('No active SuperChallenges found.');
            return Command::SUCCESS;
        }

        foreach ($challenges as $challenge) {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Challenge ID', $challenge->id],
                    ['Group', $challenge->group->name],
                    ['Description', $challenge->description],
                    ['Creator', $challenge->creator->name ?? 'Unknown'],
                    ['Prize Per Person', "{$challenge->prize_per_person} points"],
                    ['Participants', $challenge->participants->count() . '/' . $challenge->max_participants],
                    ['Deadline', $challenge->completion_deadline->format('M j, Y g:i A')],
                    ['Created', $challenge->created_at->format('M j, Y g:i A')],
                ]
            );

            if ($challenge->participants->isNotEmpty()) {
                $this->newLine();
                $this->info('  Participants:');
                foreach ($challenge->participants as $participant) {
                    $status = $participant->validation_status->value;
                    $icon = match ($status) {
                        'validated' => '✅',
                        'rejected' => '❌',
                        'pending' => $participant->completed_at ? '⏳' : '👤',
                    };
                    $this->info("    {$icon} {$participant->user->name} - {$status}");
                }
            }

            $this->newLine();
        }

        return Command::SUCCESS;
    }
}
