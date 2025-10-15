<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InvalidAnswerException;
use App\Exceptions\InvalidStakeException;
use App\Exceptions\InvalidWagerStateException;
use App\Exceptions\UserAlreadyJoinedException;
use App\Exceptions\WagerNotOpenException;
use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
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
                'deadline' => $data['deadline'],
                'status' => 'open',
            ];

            // Add type-specific fields
            $typeSpecificFields = match ($data['type']) {
                'binary' => [], // No additional fields needed
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
        string $answerValue,
        int $points
    ): WagerEntry {
        return DB::transaction(function () use ($wager, $user, $answerValue, $points) {
            // Validate wager is open
            if ($wager->status !== 'open') {
                throw new WagerNotOpenException($wager);
            }

            // Validate answer based on wager type
            $this->validateAnswer($wager, $answerValue);

            // Check if user already has an entry
            if ($wager->entries()->where('user_id', $user->id)->exists()) {
                throw new UserAlreadyJoinedException($user, $wager);
            }

            // Check points against stake
            if ($points !== $wager->stake_amount) {
                throw new InvalidStakeException($points, $wager->stake_amount);
            }

            // Deduct points
            $this->pointService->deductPoints($user, $wager->group, $points, 'wager_placed', $wager);

            // Create entry
            $entry = WagerEntry::create([
                'wager_id' => $wager->id,
                'user_id' => $user->id,
                'group_id' => $wager->group_id,
                'answer_value' => $answerValue,
                'points_wagered' => $points,
            ]);

            // Update wager stats
            $wager->increment('total_points_wagered', $points);
            $wager->increment('participants_count');

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
    private function validateAnswer(Wager $wager, string $answerValue): void
    {
        match ($wager->type) {
            'binary' => $this->validateBinaryAnswer($answerValue),
            'multiple_choice' => $this->validateMultipleChoiceAnswer($wager, $answerValue),
            'numeric' => $this->validateNumericAnswer($wager, $answerValue),
            'date' => $this->validateDateAnswer($wager, $answerValue),
        };
    }

    private function validateBinaryAnswer(string $answer): void
    {
        if (! in_array($answer, ['yes', 'no'])) {
            throw InvalidAnswerException::forBinary($answer);
        }
    }

    private function validateMultipleChoiceAnswer(Wager $wager, string $answer): void
    {
        if (! in_array($answer, $wager->options)) {
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

        if ($wager->date_min && $date < new \DateTime($wager->date_min)) {
            throw InvalidAnswerException::forDate(
                $answer,
                $wager->date_min?->format('Y-m-d'),
                $wager->date_max?->format('Y-m-d')
            );
        }

        if ($wager->date_max && $date > new \DateTime($wager->date_max)) {
            throw InvalidAnswerException::forDate(
                $answer,
                $wager->date_min?->format('Y-m-d'),
                $wager->date_max?->format('Y-m-d')
            );
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
        string $outcomeValue,
        ?string $settlementNote = null,
        ?string $settlerId = null
    ): Wager {
        return DB::transaction(function () use ($wager, $outcomeValue, $settlementNote, $settlerId) {
            if (! in_array($wager->status, ['open', 'locked'])) {
                throw new InvalidWagerStateException($wager, 'settle', ['open', 'locked']);
            }

            // Validate outcome based on type
            $this->validateAnswer($wager, $outcomeValue);

            // Update wager
            $updateData = [
                'status' => 'settled',
                'outcome_value' => $outcomeValue,
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

            return $wager->fresh();
        });
    }

    /**
     * Settle binary or multiple choice wager (exact match wins)
     */
    private function settleCategoricalWager(Wager $wager, Collection $entries, string $outcome): void
    {
        $winners = $entries->where('answer_value', $outcome);
        $losers = $entries->where('answer_value', '!=', $outcome);

        if ($winners->isEmpty()) {
            // No winners - refund everyone
            foreach ($entries as $entry) {
                $this->refundEntry($entry);
            }
            return;
        }

        $totalPot = $wager->total_points_wagered;
        $winnersTotal = $winners->sum('points_wagered');

        foreach ($winners as $entry) {
            $winnings = (int) (($entry->points_wagered / $winnersTotal) * $totalPot);
            $this->awardWinner($entry, $winnings);
        }

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

        foreach ($winners as $entry) {
            $winnings = (int) (($entry->points_wagered / $winnersTotal) * $totalPot);
            $this->awardWinner($entry, $winnings);
        }

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

        foreach ($winners as $entry) {
            $winnings = (int) (($entry->points_wagered / $winnersTotal) * $totalPot);
            $this->awardWinner($entry, $winnings);
        }

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
            $entry->wager,
            $entry
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
            $entry->wager,
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
            $entry->wager,
            $entry
        );
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
}
