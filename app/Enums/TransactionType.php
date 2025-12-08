<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case WagerPlaced = 'wager_placed';
    case WagerWon = 'wager_won';
    case WagerLost = 'wager_lost';
    case WagerRefunded = 'wager_refunded';
    case PointDecay = 'point_decay';
    case AdminAdjustment = 'admin_adjustment';
    case InitialBalance = 'initial_balance';
    case EventAttendanceBonus = 'event_attendance_bonus';
    case ChallengeHold = 'challenge_hold';
    case ChallengeCompleted = 'challenge_completed';
    case ChallengeFailed = 'challenge_failed';
    case ChallengeCancelled = 'challenge_cancelled';
    case Drop = 'drop';
    case DonationSent = 'donation_sent';
    case DonationReceived = 'donation_received';
    case SuperChallengeAcceptanceBonus = 'super_challenge_acceptance_bonus';
    case SuperChallengePrize = 'super_challenge_prize';
    case SuperChallengeValidationBonus = 'super_challenge_validation_bonus';
    case EliminationBuyIn = 'elimination_buy_in';
    case EliminationBuyInRefund = 'elimination_buy_in_refund';
    case EliminationPrize = 'elimination_prize';
    case EliminationSystemContribution = 'elimination_system_contribution';
}
