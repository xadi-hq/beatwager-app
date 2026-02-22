<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InvalidAnswerException;
use App\Exceptions\InvalidStakeException;
use App\Exceptions\InvalidWagerStateException;
use App\Exceptions\UserAlreadyJoinedException;
use App\Exceptions\WagerNotOpenException;
use App\Models\Dispute;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use App\Services\AuditEventService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WagerService
{
    public function __construct(
        private readonly PointService $pointService,
    ) {}

    /**
     * Create a new wager in a group
     */
    public function createWager(
        Group $group,
        User $creator,
        array $data
    ): Wager {
        return DB::transaction(function () use ($group, $creator, $data) {
            $wagerData = [
                'group_id' => $group->id,
                'creator_id' => $creator->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'resolution_criteria' => $data['resolution_criteria'] ?? null,
                'type' => $data['type'],
                'stake_amount' => $data['stake_amount'],
                'betting_closes_at' => $data['betting_closes_at'],
                'expected_settlement_at' => $data['expected_settlement_at'] ?? null,
                'status' => 'open',
            ];

            // Add type-specific fields
            $typeSpecificFields = match ($data['type']) {
                'binary' => [
                    'label_option_a' => $data['label_option_a'] ?? 'Yes',
                    'label_option_b' => $data['label_option_b'] ?? 'No',
                    'threshold_value' => $data['threshold_value'] ?? null,
                    'threshold_date' => $data['threshold_date'] ?? null,
                ],
                'multiple_choice' => [
                    'options' => $data['options'],
                ],
                'numeric' => [
                    'numeric_min' => $data['numeric_min'] ?? null,
                    'numeric_max' => $data['numeric_max'] ?? null,
                    'numeric_winner_type' => $data['numeric_winner_type'] ?? 'closest',
                ],
                'date' => [
                    'date_min' => $data['date_min'] ?? null,
                    'date_max' => $data['date_max'] ?? null,
                    'date_winner_type' => $data['date_winner_type'] ?? 'closest',
                ],
                'short_answer' => [
                    'type_config' => [
                        'max_length' => $data['max_length'] ?? 100,
                    ],
                ],
                'top_n_ranking' => [
                    'type_config' => [
                        'options' => $data['options'],
                        'n' => $data['n'],
                    ],
                ],
            };

            $wagerData = array_merge($wagerData, $typeSpecificFields);

            $wager = Wager::create($wagerData);

            // Audit log
            AuditService::log(
                action: 'wager.created',
                auditable: $wager,
                metadata: [
                    'group_id' => $group->id,
                    'type' => $data['type'],
                    'stake_amount' => $data['stake_amount'],
                ],
                actor: $creator
            );

            return $wager;
        });
    }

    /**
     * Place a wager entry for a user
     */
    public function placeWager(
        Wager $wager,
        User $user,
        string|array $answerValue,
        int $points
    ): WagerEntry {
        return DB::transaction(function () use ($wager, $user, $answerValue, $points) {
            // Validate wager is open
            if ($wager->status !== 'open') {
                throw new WagerNotOpenException($wager);
            }

            // Validate answer based on wager type
            $this->validateAnswer($wager, $answerValue);

            // Normalize answer to canonical casing (e.g., 'usa' -> 'USA')
            $answerValue = $this->normalizeAnswer($wager, $answerValue);

            // Check if user already has an entry
            if ($wager->entries()->where('user_id', $user->id)->exists()) {
                throw new UserAlreadyJoinedException($user, $wager);
            }

            // Check points against stake
            if ($points !== $wager->stake_amount) {
                throw new InvalidStakeException($points, $wager->stake_amount);
            }

            // Create entry first
            // Convert array answers to JSON for complex types (top_n_ranking, etc.)
            $storedAnswerValue = is_array($answerValue) ? json_encode($answerValue) : $answerValue;

            $entry = WagerEntry::create([
                'wager_id' => $wager->id,
                'user_id' => $user->id,
                'group_id' => $wager->group_id,
                'answer_value' => $storedAnswerValue,
                'points_wagered' => $points,
            ]);

            // Deduct points and link to entry
            $this->pointService->deductPoints($user, $wager->group, $points, 'wager_placed', $entry);

            // Update wager stats
            $wager->increment('total_points_wagered', $points);
            $wager->increment('participants_count');

            // Update last_wager_joined_at for decay tracking and clear warning timestamp
            DB::table('group_user')
                ->where('user_id', $user->id)
                ->where('group_id', $wager->group_id)
                ->update([
                    'last_wager_joined_at' => now(),
                    'decay_warning_sent_at' => null, // Clear warning for fresh cycle
                ]);

            // Audit log
            AuditService::log(
                action: 'wager.joined',
                auditable: $wager,
                metadata: [
                    'entry_id' => $entry->id,
                    'answer_value' => $answerValue,
                    'points_wagered' => $points,
                ],
                actor: $user
            );

            return $entry;
        });
    }

    /**
     * Validate answer based on wager type
     */
    private function validateAnswer(Wager $wager, string|array $answerValue): void
    {
        match ($wager->type) {
            'binary' => $this->validateBinaryAnswer($answerValue),
            'multiple_choice' => $this->validateMultipleChoiceAnswer($wager, $answerValue),
            'numeric' => $this->validateNumericAnswer($wager, $answerValue),
            'date' => $this->validateDateAnswer($wager, $answerValue),
            'short_answer' => $this->validateShortAnswer($wager, $answerValue),
            'top_n_ranking' => $this->validateRanking($wager, $answerValue),
        };
    }

    /**
     * Normalize answer to canonical casing for storage.
     * Must be called after validateAnswer() succeeds.
     */
    private function normalizeAnswer(Wager $wager, string|array $answerValue): string|array
    {
        return match ($wager->type) {
            'binary' => strtolower($answerValue),
            'multiple_choice' => collect($wager->options)->first(
                fn ($option) => strcasecmp($option, $answerValue) === 0
            ) ?? $answerValue,
            default => $answerValue,
        };
    }

    private function validateBinaryAnswer(string $answer): void
    {
        if (! in_array(strtolower($answer), ['yes', 'no'])) {
            throw InvalidAnswerException::forBinary($answer);
        }
    }

    private function validateMultipleChoiceAnswer(Wager $wager, string $answer): void
    {
        $match = collect($wager->options)->first(
            fn ($option) => strcasecmp($option, $answer) === 0
        );

        if ($match === null) {
            throw InvalidAnswerException::forMultipleChoice($answer, $wager->options);
        }
    }

    private function validateNumericAnswer(Wager $wager, string $answer): void
    {
        // Validate it's a valid integer string (no decimals, no scientific notation)
        if (!filter_var($answer, FILTER_VALIDATE_INT)) {
            throw InvalidAnswerException::forNumeric($answer, $wager->numeric_min, $wager->numeric_max);
        }

        $number = (int) $answer;

        if ($wager->numeric_min !== null && $number < $wager->numeric_min) {
            throw InvalidAnswerException::forNumeric($answer, $wager->numeric_min, $wager->numeric_max);
        }

        if ($wager->numeric_max !== null && $number > $wager->numeric_max) {
            throw InvalidAnswerException::forNumeric($answer, $wager->numeric_min, $wager->numeric_max);
        }
    }

    private function validateDateAnswer(Wager $wager, string $answer): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $answer);
        if (! $date) {
            throw InvalidAnswerException::forDate(
                $answer,
                $wager->date_min?->format('Y-m-d'),
                $wager->date_max?->format('Y-m-d')
            );
        }

        if ($wager->date_min && $date < new \DateTime($wager->date_min->format('Y-m-d'))) {
            throw InvalidAnswerException::forDate(
                $answer,
                $wager->date_min?->format('Y-m-d'),
                $wager->date_max?->format('Y-m-d')
            );
        }

        if ($wager->date_max && $date > new \DateTime($wager->date_max->format('Y-m-d'))) {
            throw InvalidAnswerException::forDate(
                $answer,
                $wager->date_min?->format('Y-m-d'),
                $wager->date_max?->format('Y-m-d')
            );
        }
    }

    private function validateShortAnswer(Wager $wager, string|array $answer): void
    {
        // For settlement: array of entry IDs (can be empty if nobody won)
        if (is_array($answer)) {
            // This is settlement context - array of winning entry IDs
            return;
        }

        // For joining: must be a non-empty string
        if (!is_string($answer)) {
            throw new InvalidAnswerException('Short answer must be a string');
        }

        $answer = trim($answer);
        if (empty($answer)) {
            throw new InvalidAnswerException('Short answer cannot be empty');
        }

        $config = $wager->getTypeConfig();
        $maxLength = $config['max_length'] ?? 100;

        if (mb_strlen($answer) > $maxLength) {
            throw new InvalidAnswerException("Short answer must be {$maxLength} characters or less");
        }
    }

    private function validateRanking(Wager $wager, string|array $answer): void
    {
        if (!is_array($answer)) {
            throw new InvalidAnswerException('Ranking must be an array');
        }

        $config = $wager->getTypeConfig();
        $expectedN = $config['n'] ?? 3;
        $validOptions = $config['options'] ?? [];

        if (count($answer) !== $expectedN) {
            throw new InvalidAnswerException("Ranking must contain exactly {$expectedN} items");
        }

        // Check all items are valid options
        foreach ($answer as $item) {
            if (!in_array($item, $validOptions)) {
                throw new InvalidAnswerException("Invalid option in ranking: {$item}");
            }
        }

        // Check no duplicates
        if (count($answer) !== count(array_unique($answer))) {
            throw new InvalidAnswerException('Ranking cannot contain duplicate items');
        }
    }

    /**
     * Lock a wager (no more entries allowed)
     */
    public function lockWager(Wager $wager): Wager
    {
        if ($wager->status !== 'open') {
            throw new InvalidWagerStateException($wager, 'lock', ['open']);
        }

        $wager->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);

        return $wager->fresh();
    }

    /**
     * Settle a wager with the outcome
     */
    public function settleWager(
        Wager $wager,
        string|array $outcomeValue,
        ?string $settlementNote = null,
        ?string $settlerId = null
    ): Wager {
        return DB::transaction(function () use ($wager, $outcomeValue, $settlementNote, $settlerId) {
            if (! in_array($wager->status, ['open', 'locked'])) {
                throw new InvalidWagerStateException($wager, 'settle', ['open', 'locked']);
            }

            // Validate outcome based on type
            $this->validateAnswer($wager, $outcomeValue);

            // Normalize outcome to canonical casing (e.g., 'usa' -> 'USA')
            $outcomeValue = $this->normalizeAnswer($wager, $outcomeValue);

            // Update wager
            // Convert array outcomes to JSON for complex types (top_n_ranking, etc.)
            $storedOutcomeValue = is_array($outcomeValue) ? json_encode($outcomeValue) : $outcomeValue;

            $updateData = [
                'status' => 'settled',
                'outcome_value' => $storedOutcomeValue,
                'settlement_note' => $settlementNote,
                'settled_at' => now(),
            ];

            if ($settlerId) {
                $updateData['settler_id'] = $settlerId;
            }

            $wager->update($updateData);

            // Process all entries based on wager type (eager load relationships to avoid N+1)
            $entries = $wager->entries()->with(['user', 'group'])->get();
            match ($wager->type) {
                'binary', 'multiple_choice' => $this->settleCategoricalWager($wager, $entries, $outcomeValue),
                'numeric' => $this->settleNumericWager($wager, $entries, (int) $outcomeValue),
                'date' => $this->settleDateWager($wager, $entries, $outcomeValue),
                'short_answer' => $this->settleShortAnswerWager($wager, $entries, $outcomeValue),
                'top_n_ranking' => $this->settleRankingWager($wager, $entries, $outcomeValue),
            };

            // Audit log
            $settler = $settlerId ? User::find($settlerId) : null;
            AuditService::log(
                action: 'wager.settled',
                auditable: $wager,
                metadata: [
                    'outcome_value' => $outcomeValue,
                    'settlement_note' => $settlementNote,
                    'winners_count' => $wager->entries()->where('is_winner', true)->count(),
                    'total_pot' => $wager->total_points_wagered,
                ],
                actor: $settler
            );

            // Create audit events for LLM context
            $this->createAuditEvents($wager);

            return $wager->fresh();
        });
    }

    /**
     * Settle binary or multiple choice wager (exact match wins)
     */
    private function settleCategoricalWager(Wager $wager, Collection $entries, string $outcome): void
    {
        $winners = $entries->filter(fn ($entry) => strcasecmp($entry->answer_value, $outcome) === 0);
        $losers = $entries->filter(fn ($entry) => strcasecmp($entry->answer_value, $outcome) !== 0);

        if ($winners->isEmpty()) {
            // No winners - refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $totalPot = $wager->total_points_wagered;
        $winnersTotal = $winners->sum('points_wagered');

        $this->distributePotToWinners($winners, $totalPot, $winnersTotal, $wager->group);

        foreach ($losers as $entry) {
            $this->recordLoss($entry);
        }
    }

    /**
     * Settle numeric wager (closest guess wins)
     */
    private function settleNumericWager(Wager $wager, Collection $entries, int $outcome): void
    {
        // Calculate distances
        foreach ($entries as $entry) {
            $distance = abs((int) $entry->answer_value - $outcome);
            $entry->update(['numeric_distance' => $distance]);
        }

        if ($wager->numeric_winner_type === 'exact') {
            // Only exact matches win
            $winners = $entries->where('numeric_distance', 0);
        } else {
            // Closest wins
            $minDistance = $entries->min('numeric_distance');
            $winners = $entries->where('numeric_distance', $minDistance);
        }

        if ($winners->isEmpty()) {
            // No winners - refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $totalPot = $wager->total_points_wagered;
        $winnersTotal = $winners->sum('points_wagered');

        $this->distributePotToWinners($winners, $totalPot, $winnersTotal, $wager->group);

        $losers = $entries->whereNotIn('id', $winners->pluck('id'));
        foreach ($losers as $entry) {
            $this->recordLoss($entry);
        }
    }

    /**
     * Settle date wager (closest guess wins)
     */
    private function settleDateWager(Wager $wager, Collection $entries, string $outcome): void
    {
        $outcomeDate = new \DateTime($outcome);

        // Calculate distances in days
        foreach ($entries as $entry) {
            $entryDate = new \DateTime($entry->answer_value);
            $distance = abs($outcomeDate->diff($entryDate)->days);
            $entry->update(['date_distance_days' => $distance]);
        }

        if ($wager->date_winner_type === 'exact') {
            // Only exact matches win
            $winners = $entries->where('date_distance_days', 0);
        } else {
            // Closest wins
            $minDistance = $entries->min('date_distance_days');
            $winners = $entries->where('date_distance_days', $minDistance);
        }

        if ($winners->isEmpty()) {
            // No winners - refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $totalPot = $wager->total_points_wagered;
        $winnersTotal = $winners->sum('points_wagered');

        $this->distributePotToWinners($winners, $totalPot, $winnersTotal, $wager->group);

        $losers = $entries->whereNotIn('id', $winners->pluck('id'));
        foreach ($losers as $entry) {
            $this->recordLoss($entry);
        }
    }

    /**
     * Settle short answer wager
     * Outcome value is an array of winning entry IDs selected by settler
     */
    private function settleShortAnswerWager(Wager $wager, Collection $entries, string|array $outcome): void
    {
        // Outcome should be array of entry IDs that are considered matching
        $winningEntryIds = is_array($outcome) ? $outcome : json_decode($outcome, true);

        if (!is_array($winningEntryIds)) {
            // Fallback: treat as no winners
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $winners = $entries->whereIn('id', $winningEntryIds);
        $losers = $entries->whereNotIn('id', $winningEntryIds);

        if ($winners->isEmpty()) {
            // No winners - refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $totalPot = $wager->total_points_wagered;
        $winnersTotal = $winners->sum('points_wagered');

        $this->distributePotToWinners($winners, $totalPot, $winnersTotal, $wager->group);

        foreach ($losers as $entry) {
            $this->recordLoss($entry);
        }
    }

    /**
     * Settle top N ranking wager with position-based scoring
     *
     * Scoring: Each correct position = (100 / N)% of total score
     * Example for top-3: Position 1 correct = 33%, Position 2 correct = 33%, etc.
     *
     * Winner determination (threshold-based):
     * - If highest score = 100%: Only perfect scores win
     * - If highest score >= 67%: Anyone with 67%+ wins (2+ positions for top-3)
     * - If highest score >= 34%: Anyone with 34%+ wins (1+ position for top-3)
     * - If highest score < 34%: Only highest scorers win
     * - If nobody scores > 0%: Refund all stakes
     */
    private function settleRankingWager(Wager $wager, Collection $entries, string|array $outcome): void
    {
        // Outcome should be the actual ranking as array
        $actualRanking = is_array($outcome) ? $outcome : json_decode($outcome, true);

        if (!is_array($actualRanking)) {
            // Fallback: refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $totalPositions = count($actualRanking);
        $scores = collect();

        // Calculate position-based score for each entry
        foreach ($entries as $entry) {
            $userRanking = is_string($entry->answer_value)
                ? json_decode($entry->answer_value, true)
                : $entry->answer_value;

            if (!is_array($userRanking)) {
                $scores->put($entry->id, 0);
                continue;
            }

            // Count correct positions
            $correctPositions = 0;
            for ($i = 0; $i < min(count($userRanking), $totalPositions); $i++) {
                if (isset($actualRanking[$i]) && $userRanking[$i] === $actualRanking[$i]) {
                    $correctPositions++;
                }
            }

            // Calculate score as percentage (0-100)
            $scorePercent = ($correctPositions / $totalPositions) * 100;
            $scores->put($entry->id, $scorePercent);
        }

        // Determine winner threshold based on highest score
        $highestScore = $scores->max();

        if ($highestScore < 1) {
            // Nobody got even close - refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        // Set threshold: if someone got 100%, only 100% wins
        // Otherwise, top tier wins (67%+, 34%+, or any score > 0)
        if ($highestScore >= 100) {
            $threshold = 100;
        } elseif ($highestScore >= 67) {
            $threshold = 67;
        } elseif ($highestScore >= 34) {
            $threshold = 34;
        } else {
            // Highest score is below 34% - only the highest scorers win
            $threshold = $highestScore;
        }

        // Find winners at or above threshold
        $winners = collect();
        foreach ($entries as $entry) {
            $score = $scores->get($entry->id);
            if ($score >= $threshold) {
                $winners->push($entry);
            }
        }

        if ($winners->isEmpty()) {
            // Shouldn't happen, but safety fallback
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        // Distribute pot among winners proportionally to their wagers
        $totalPot = $wager->total_points_wagered;
        $winnersTotal = $winners->sum('points_wagered');

        $this->distributePotToWinners($winners, $totalPot, $winnersTotal, $wager->group);

        // Record losses for non-winners
        $losers = $entries->whereNotIn('id', $winners->pluck('id'));
        foreach ($losers as $entry) {
            $this->recordLoss($entry);
        }
    }

    private function awardWinner(WagerEntry $entry, int $winnings): void
    {
        $entry->update([
            'result' => 'won',
            'is_winner' => true,
            'points_won' => $winnings,
        ]);

        $this->pointService->awardPoints(
            $entry->user,
            $entry->group,
            $winnings,
            'wager_won',
            $entry  // Pass the entry only
        );
    }

    private function recordLoss(WagerEntry $entry): void
    {
        $entry->update([
            'result' => 'lost',
            'points_lost' => $entry->points_wagered,
        ]);

        $this->pointService->recordLoss(
            $entry->user,
            $entry->group,
            $entry->points_wagered,
            $entry
        );
    }

    private function refundEntry(WagerEntry $entry): void
    {
        $entry->update(['result' => 'refunded']);

        $this->pointService->refundPoints(
            $entry->user,
            $entry->group,
            $entry->points_wagered,
            $entry
        );
    }

    /**
     * Distribute pot to winners proportionally and add remainder to house pot.
     * Returns the total amount actually distributed.
     *
     * @param Collection<int, WagerEntry>|\Illuminate\Support\Collection<int|string, mixed> $winners
     * @param Group|\Illuminate\Database\Eloquent\Model|null $group
     */
    private function distributePotToWinners(Collection|\Illuminate\Support\Collection $winners, int $totalPot, int $winnersTotal, Group|\Illuminate\Database\Eloquent\Model|null $group): int
    {
        if (!$group instanceof Group) {
            // Fallback: distribute without house pot handling (shouldn't happen in practice)
            $totalDistributed = 0;
            foreach ($winners as $entry) {
                $winnings = (int) (($entry->points_wagered / $winnersTotal) * $totalPot);
                $this->awardWinner($entry, $winnings);
                $totalDistributed += $winnings;
            }
            return $totalDistributed;
        }

        $totalDistributed = 0;

        foreach ($winners as $entry) {
            $winnings = (int) (($entry->points_wagered / $winnersTotal) * $totalPot);
            $this->awardWinner($entry, $winnings);
            $totalDistributed += $winnings;
        }

        // Add remainder to house pot (due to integer truncation)
        $remainder = $totalPot - $totalDistributed;
        if ($remainder > 0) {
            $group->increment('house_pot', $remainder);
        }

        return $totalDistributed;
    }

    /**
     * Cancel a wager and refund all entries
     */
    public function cancelWager(Wager $wager): Wager
    {
        return DB::transaction(function () use ($wager) {
            if (! in_array($wager->status, ['open', 'locked'])) {
                throw new InvalidWagerStateException($wager, 'cancel', ['open', 'locked']);
            }

            $wager->update(['status' => 'cancelled']);

            // Refund all entries (eager load relationships to avoid N+1)
            $entries = $wager->entries()->with(['user', 'group', 'wager'])->get();
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }

            return $wager->fresh();
        });
    }

    /**
     * Create audit events for LLM context after wager settlement
     */
    private function createAuditEvents(Wager $wager): void
    {
        // Refresh wager to get latest entries with results
        $wager->load(['entries.user', 'group']);

        // For 1v1 wagers, create detailed winner/loser events
        if ($wager->entries->count() === 2) {
            $winner = $wager->entries->firstWhere('is_winner', true);
            $loser = $wager->entries->firstWhere('is_winner', false);

            if ($winner && $loser) {
                $pointsGained = $winner->points_won - $winner->points_wagered;

                AuditEventService::wagerWon(
                    group: $wager->group,
                    winner: $winner->user,
                    loser: $loser->user,
                    points: $pointsGained,
                    wagerTitle: $wager->title,
                    wagerId: $wager->id
                );
            }
            return;
        }

        // For multi-participant wagers, create a generic summary
        $winners = $wager->entries->where('is_winner', true);
        $losers = $wager->entries->where('is_winner', false);

        if ($winners->isNotEmpty()) {
            $winnerNames = $winners->pluck('user.username')->join(', ');
            $totalWon = $winners->sum(fn($e) => $e->points_won - $e->points_wagered);

            $summary = $winners->count() === 1
                ? "{$winnerNames} won '{$wager->title}' and gained {$totalWon} points"
                : "{$winnerNames} won '{$wager->title}' and split {$totalWon} points";

            AuditEventService::create(
                group: $wager->group,
                eventType: 'wager.multi_winner',
                summary: $summary,
                participants: $winners->map(fn($e) => [
                    'user_id' => $e->user_id,
                    'username' => $e->user->username,
                    'role' => 'winner',
                ])->toArray(),
                impact: ['total_points_won' => $totalWon],
                metadata: ['wager_id' => $wager->id]
            );
        } elseif ($losers->isNotEmpty()) {
            // All refunded or no winners
            $summary = "'{$wager->title}' ended with no winners - all {$wager->entries->count()} participants were refunded";

            AuditEventService::create(
                group: $wager->group,
                eventType: 'wager.no_winner',
                summary: $summary,
                participants: [],
                impact: ['refund_count' => $wager->entries->count()],
                metadata: ['wager_id' => $wager->id]
            );
        }
    }

    /**
     * Reverse a wager settlement and re-settle with corrected outcome.
     * Used when a dispute confirms fraud.
     */
    public function reverseAndResettleWager(Wager $wager, string $correctedOutcome, Dispute $dispute): void
    {
        DB::transaction(function () use ($wager, $correctedOutcome, $dispute) {
            // Step 1: Reverse all settlement transactions
            $this->reverseSettlementTransactions($wager, $dispute);

            // Step 2: Reset entry results
            $wager->entries()->update([
                'result' => null,
                'is_winner' => false,
                'points_won' => 0,
                'points_lost' => 0,
            ]);

            // Step 3: Re-settle with correct outcome
            $wager->update([
                'status' => 'locked', // Temporarily unlock for settlement
                'outcome_value' => null,
                'settled_at' => null,
            ]);

            // Re-run settlement
            $this->settleWager(
                $wager,
                $correctedOutcome,
                "Corrected via dispute #{$dispute->id}",
                null // No settler for dispute correction
            );

            // Audit log
            AuditService::log(
                action: 'wager.dispute_corrected',
                auditable: $wager,
                metadata: [
                    'dispute_id' => $dispute->id,
                    'original_outcome' => $dispute->original_outcome,
                    'corrected_outcome' => $correctedOutcome,
                ]
            );
        });
    }

    /**
     * Clear a wager settlement for premature settlement cases.
     * Bans the specified user from the wager.
     */
    public function clearSettlementAndBanUser(Wager $wager, string $bannedUserId, Dispute $dispute): void
    {
        DB::transaction(function () use ($wager, $bannedUserId, $dispute) {
            // Step 1: Reverse all settlement transactions
            $this->reverseSettlementTransactions($wager, $dispute);

            // Step 2: Reset entry results
            $wager->entries()->update([
                'result' => null,
                'is_winner' => false,
                'points_won' => 0,
                'points_lost' => 0,
            ]);

            // Step 3: Ban the user - remove their entry and refund
            $bannedEntry = $wager->entries()->where('user_id', $bannedUserId)->first();
            if ($bannedEntry) {
                // Create refund transaction for the banned user
                $this->pointService->awardPoints(
                    User::find($bannedUserId),
                    $wager->group,
                    $bannedEntry->points_wagered,
                    TransactionType::DisputeRefund->value,
                    $dispute
                );

                // Remove the entry
                $bannedEntry->delete();

                // Update wager stats
                $wager->decrement('total_points_wagered', $bannedEntry->points_wagered);
                $wager->decrement('participants_count');
            }

            // Step 4: Reset wager to open state for re-settlement
            // Using 'open' instead of 'locked' so it appears in dashboard and can be settled
            $wager->update([
                'status' => 'open',
                'outcome_value' => null,
                'settlement_note' => null,
                'settler_id' => null,
                'settled_at' => null,
            ]);

            // Audit log
            AuditService::log(
                action: 'wager.settlement_cleared',
                auditable: $wager,
                metadata: [
                    'dispute_id' => $dispute->id,
                    'banned_user_id' => $bannedUserId,
                    'reason' => 'premature_settlement',
                ]
            );
        });
    }

    /**
     * Reverse all settlement-related transactions for a wager.
     */
    private function reverseSettlementTransactions(Wager $wager, Dispute $dispute): void
    {
        $entries = $wager->entries()->with('user')->get();

        foreach ($entries as $entry) {
            // Find settlement transactions for this entry (won or refunded)
            $settlementTransactions = Transaction::where('transactionable_type', WagerEntry::class)
                ->where('transactionable_id', $entry->id)
                ->whereIn('type', ['wager_won', 'wager_refunded'])
                ->get();

            foreach ($settlementTransactions as $transaction) {
                if ($transaction->amount > 0) {
                    // This was a credit (won/refunded) - need to deduct
                    $this->pointService->deductPoints(
                        $entry->user,
                        $wager->group,
                        $transaction->amount,
                        TransactionType::DisputeCorrection->value,
                        $dispute
                    );
                }
            }
        }
    }
}
