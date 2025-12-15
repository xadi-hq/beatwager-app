<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Models\Group;
use App\Models\GroupEventAttendance;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when event attendance is recorded.
 *
 * Triggers:
 * - event_attended: For users who attended
 * - event_missed: For users who did not attend (no-shows)
 */
class CheckBadgesOnAttendanceRecorded implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(AttendanceRecorded $event): void
    {
        $groupEvent = $event->event;

        /** @var Group|null $group */
        $group = $groupEvent->group;

        if ($group === null) {
            return;
        }

        // Get all attendance records for this event
        $attendanceRecords = $groupEvent->attendance()->with('user')->get();

        /** @var GroupEventAttendance $record */
        foreach ($attendanceRecords as $record) {
            /** @var User|null $user */
            $user = $record->user;

            if ($user === null) {
                continue;
            }

            if ($record->attended) {
                // Check attended badges
                $this->badgeService->checkAndAward(
                    $user,
                    'event_attended',
                    $group,
                    ['event_id' => $groupEvent->id]
                );
            } else {
                // Check no-show badges
                $this->badgeService->checkAndAward(
                    $user,
                    'event_missed',
                    $group,
                    ['event_id' => $groupEvent->id]
                );
            }
        }
    }
}
