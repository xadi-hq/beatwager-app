<?php

namespace App\Console\Commands;

use App\Jobs\SendBirthdayReminders;
use Illuminate\Console\Command;

class TestBirthdayReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:birthday-reminders
                            {--user= : Test with specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test birthday reminder system (manually trigger the job)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎂 Testing Birthday Reminder System...');
        $this->newLine();

        if ($userId = $this->option('user')) {
            $user = \App\Models\User::find($userId);

            if (!$user) {
                $this->error("User {$userId} not found");
                return Command::FAILURE;
            }

            if (!$user->birthday) {
                $this->error("{$user->name} has no birthday set");
                return Command::FAILURE;
            }

            $this->info("User: {$user->name}");
            $this->info("Birthday: {$user->birthday->format('F j, Y')}");
            $this->newLine();

            // Check if birthday is 7 days from now
            $daysUntil = now()->diffInDays($user->birthday->setYear(now()->year), false);

            if ($daysUntil < 0) {
                // Birthday already passed this year, check next year
                $daysUntil = now()->diffInDays($user->birthday->setYear(now()->year + 1), false);
            }

            $this->info("Days until birthday: {$daysUntil}");

            if ($daysUntil != 7) {
                $this->warn("⚠️  Birthday is not exactly 7 days away (currently {$daysUntil} days)");
                $this->warn("The job will only send reminders for birthdays exactly 7 days out.");
            }
            $this->newLine();
        }

        $this->info('Running SendBirthdayReminders job...');

        try {
            $job = new SendBirthdayReminders();
            $job->handle(
                app(\App\Services\MessageService::class),
                app(\App\Services\MessageTrackingService::class)
            );

            $this->info('✅ Job completed successfully!');
            $this->info('Check logs for details on reminders sent.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Job failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
