<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Group;
use App\Models\ScheduledMessage;
use App\Models\User;
use App\Services\MessageService;
use App\Services\MessageTrackingService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendBirthdayReminders implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     * Check for upcoming birthdays (7 days out) and send reminders to groups
     */
    public function handle(MessageService $messageService, MessageTrackingService $trackingService): void
    {
        Log::info('SendBirthdayReminders job started');

        $remindersSent = 0;
        $upcomingBirthdays = $this->getUpcomingBirthdays();

        foreach ($upcomingBirthdays as $birthdayData) {
            $user = $birthdayData['user'];
            $daysUntil = $birthdayData['days_until'];

            // Get all groups this user belongs to
            $groups = $user->groups()
                ->where('is_active', true)
                ->get();

            foreach ($groups as $group) {
                try {
                    // Check if group wants birthday reminders (default: true)
                    $notificationPreferences = $group->notification_preferences ?? [];
                    if (isset($notificationPreferences['birthday_reminders']) && !$notificationPreferences['birthday_reminders']) {
                        continue; // Skip if disabled
                    }

                    // Check if already sent reminder for this birthday this year
                    $cacheKey = "birthday_reminder:{$group->id}:{$user->id}:" . now()->year;
                    if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                        continue; // Already sent
                    }

                    // Generate and send reminder message
                    $message = $messageService->birthdayReminder($group, $user, $daysUntil);
                    $group->sendMessage($message);

                    // Track the message
                    $trackingService->recordMessage(
                        $group,
                        'scheduled.birthday_reminder',
                        "Birthday reminder: {$user->name} in {$daysUntil} days",
                        'birthday_reminder',
                        $user->id,
                        [
                            'user_id' => $user->id,
                            'days_until' => $daysUntil,
                            'birthday_date' => $user->birthday->format('Y-m-d'),
                        ]
                    );

                    // Cache until next year to prevent duplicate reminders
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addYear());

                    Log::info("Sent birthday reminder for {$user->name} to group {$group->id} ({$daysUntil} days)");
                    $remindersSent++;

                } catch (\Exception $e) {
                    Log::error("Failed to send birthday reminder for {$user->name} to group {$group->id}: " . $e->getMessage(), [
                        'user_id' => $user->id,
                        'group_id' => $group->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info("SendBirthdayReminders job completed: {$remindersSent} reminders sent");
    }

    /**
     * Get users with birthdays coming up in 7 days
     *
     * @return array Array of ['user' => User, 'days_until' => int]
     */
    private function getUpcomingBirthdays(): array
    {
        $targetDate = now()->addDays(7);
        $targetMonth = $targetDate->month;
        $targetDay = $targetDate->day;

        // Find users with birthdays on this date (ignoring year)
        $users = User::whereNotNull('birthday')
            ->whereRaw('EXTRACT(MONTH FROM birthday) = ?', [$targetMonth])
            ->whereRaw('EXTRACT(DAY FROM birthday) = ?', [$targetDay])
            ->get();

        return $users->map(function ($user) use ($targetDate) {
            return [
                'user' => $user,
                'days_until' => 7,
            ];
        })->toArray();
    }
}
