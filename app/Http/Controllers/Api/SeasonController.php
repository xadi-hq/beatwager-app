<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupSeason;
use App\Services\MessageService;
use App\Services\SeasonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function __construct(
        private SeasonService $seasonService,
        private MessageService $messageService
    ) {}

    /**
     * List all seasons for a group
     */
    public function index(Group $group): JsonResponse
    {
        $this->authorize('view', $group);

        $seasons = $group->seasons()
            ->orderBy('season_number', 'desc')
            ->get();

        return response()->json([
            'seasons' => $seasons,
            'current_season' => $group->currentSeason,
        ]);
    }

    /**
     * Get details for a specific season
     */
    public function show(Group $group, GroupSeason $season): JsonResponse
    {
        $this->authorize('view', $group);

        if ($season->group_id !== $group->id) {
            abort(404, 'Season not found for this group');
        }

        return response()->json($season->load('group'));
    }

    /**
     * Create a new season for the group
     */
    public function store(Request $request, Group $group): JsonResponse
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'ends_at' => 'nullable|date|after:now',
        ]);

        $endsAt = isset($validated['ends_at']) ? \Carbon\Carbon::parse($validated['ends_at']) : null;

        $season = $this->seasonService->createSeason($group, $endsAt);

        // Send announcement to group (optional - could be done via event)
        // For now, keeping it simple without announcement on creation

        return response()->json([
            'message' => 'Season created successfully',
            'season' => $season,
        ], 201);
    }

    /**
     * End the current season
     */
    public function end(Group $group): JsonResponse
    {
        $this->authorize('update', $group);

        if (!$group->currentSeason) {
            return response()->json([
                'error' => 'No active season to end',
            ], 400);
        }

        $endedSeason = $this->seasonService->endSeason($group);

        // Send season recap message to group
        $message = $this->messageService->seasonEnded($group, $endedSeason);
        $group->sendMessage($message);

        return response()->json([
            'message' => 'Season ended successfully',
            'season' => $endedSeason,
        ]);
    }
}
