<?php

namespace App\Console\Commands;

use App\Models\LlmUsageDaily;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AggregateLLMMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llm:aggregate {--date= : Date to aggregate (YYYY-MM-DD, defaults to yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate LLM usage metrics from operational logs into daily summary';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Determine which date to aggregate
        $dateStr = $this->option('date') ?: Carbon::yesterday()->toDateString();
        $date = Carbon::parse($dateStr);

        $this->info("Aggregating LLM metrics for {$date->toDateString()}...");

        // Read operational log file (daily driver creates dated files)
        $logPath = storage_path("logs/operational-{$date->toDateString()}.log");

        if (!file_exists($logPath)) {
            $this->warn("No operational log file found for {$date->toDateString()}. Skipping aggregation.");
            return Command::SUCCESS;
        }

        // Parse logs and aggregate by group
        $metrics = $this->parseLogsForDate($logPath, $date);

        if (empty($metrics)) {
            $this->info('No LLM events found for this date.');
            return Command::SUCCESS;
        }

        // Upsert metrics into database
        $aggregated = 0;
        $skipped = 0;
        $invalidGroups = [];

        foreach ($metrics as $groupId => $data) {
            // Validate UUID format first
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $groupId)) {
                $this->error("❌ Invalid UUID format for group_id: {$groupId}");
                $skipped++;
                continue;
            }

            // Verify group exists in database
            if (!\App\Models\Group::where('id', $groupId)->exists()) {
                $this->warn("⚠️  Group {$groupId} not found in database");
                $this->line("   → Calls: {$data['total_calls']}, Cost: \${$data['estimated_cost_usd']}, Providers: " . implode(', ', array_keys($data['providers_breakdown'])));

                // Store details for investigation
                $invalidGroups[] = [
                    'group_id' => $groupId,
                    'total_calls' => $data['total_calls'],
                    'estimated_cost_usd' => $data['estimated_cost_usd'],
                    'providers' => $data['providers_breakdown'],
                    'message_types' => $data['message_types'],
                ];
                $skipped++;
                continue;
            }

            LlmUsageDaily::updateOrCreate(
                [
                    'group_id' => $groupId,
                    'date' => $date,
                ],
                [
                    'total_calls' => $data['total_calls'],
                    'cached_calls' => $data['cached_calls'],
                    'fallback_calls' => $data['fallback_calls'],
                    'estimated_cost_usd' => $data['estimated_cost_usd'],
                    'providers_breakdown' => $data['providers_breakdown'],
                    'message_types' => $data['message_types'],
                ]
            );
            $aggregated++;
        }

        $this->info("✅ Aggregated metrics for {$aggregated} groups.");

        if ($skipped > 0) {
            $this->warn("⚠️  Skipped {$skipped} groups that were not found in the database.");

            // Log details for investigation
            \Log::warning('LLM aggregation skipped groups', [
                'date' => $date->toDateString(),
                'skipped_count' => $skipped,
                'invalid_groups' => $invalidGroups,
            ]);

            $this->newLine();
            $this->warn("💡 Investigation tips:");
            $this->line("   1. Check if these groups exist: SELECT id FROM groups WHERE id IN ('" . implode("','", array_column($invalidGroups, 'group_id')) . "');");
            $this->line("   2. Check if groups were recently deleted (audit logs)");
            $this->line("   3. Verify log file is from production environment");
            $this->line("   4. Look for database transaction rollbacks around this time");
        }

        return Command::SUCCESS;
    }

    /**
     * Parse operational log file for a specific date
     */
    private function parseLogsForDate(string $logPath, Carbon $date): array
    {
        $metrics = [];
        $handle = fopen($logPath, 'r');

        if (!$handle) {
            return [];
        }

        while (($line = fgets($handle)) !== false) {
            // Parse JSON log line
            $entry = json_decode($line, true);

            if (!$entry || !isset($entry['message'])) {
                continue;
            }

            // Only process LLM events
            if (!str_starts_with($entry['message'], 'llm.')) {
                continue;
            }

            // Check if log entry is for our target date
            $logDate = Carbon::parse($entry['datetime'] ?? $entry['timestamp'] ?? null);
            if (!$logDate || !$logDate->isSameDay($date)) {
                continue;
            }

            $groupId = $entry['context']['group_id'] ?? null;
            if (!$groupId) {
                continue;
            }

            // Initialize group metrics if not exists
            if (!isset($metrics[$groupId])) {
                $metrics[$groupId] = [
                    'total_calls' => 0,
                    'cached_calls' => 0,
                    'fallback_calls' => 0,
                    'estimated_cost_usd' => 0.0,
                    'providers_breakdown' => [],
                    'message_types' => [],
                ];
            }

            // Aggregate based on event type
            $event = $entry['message'];  // Event is in 'message' field
            $context = $entry['context'];

            if ($event === 'llm.generation.success') {
                $metrics[$groupId]['total_calls']++;
                $cost = $context['estimated_cost_usd'] ?? 0;
                $metrics[$groupId]['estimated_cost_usd'] += $cost;

                // Track provider
                $provider = $context['provider'] ?? 'unknown';
                $metrics[$groupId]['providers_breakdown'][$provider]
                    = ($metrics[$groupId]['providers_breakdown'][$provider] ?? 0) + 1;

                // Track message type
                $messageKey = $context['message_key'] ?? 'unknown';
                $metrics[$groupId]['message_types'][$messageKey]
                    = ($metrics[$groupId]['message_types'][$messageKey] ?? 0) + 1;
            }

            if ($event === 'llm.generation.cached') {
                $metrics[$groupId]['cached_calls']++;
            }

            if ($event === 'llm.fallback_used') {
                $metrics[$groupId]['fallback_calls']++;
            }
        }

        fclose($handle);

        return $metrics;
    }
}
