<?php

namespace App\Http\Middleware;

use App\Models\Challenge;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\Wager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserInGroup
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user is a member of the group associated with the resource.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Extract group from request
        $group = $this->extractGroupFromRequest($request);

        if (!$group) {
            // No group context - allow request (e.g., viewing own profile)
            return $next($request);
        }

        // Check if user is member of this group
        if (!$user->groups()->where('groups.id', $group->id)->exists()) {
            abort(403, 'You are not a member of this group');
        }

        return $next($request);
    }

    /**
     * Extract group from request based on route parameters
     */
    private function extractGroupFromRequest(Request $request): ?Group
    {
        // Direct group_id in request data (for store operations)
        if ($request->has('group_id')) {
            return Group::find($request->input('group_id'));
        }

        // Group from route parameter
        if ($request->route('group')) {
            $groupParam = $request->route('group');
            return $groupParam instanceof Group ? $groupParam : Group::find($groupParam);
        }

        // Wager from route parameter
        if ($request->route('wager')) {
            $wagerParam = $request->route('wager');
            $wager = $wagerParam instanceof Wager ? $wagerParam : Wager::find($wagerParam);
            return $wager?->group;
        }

        // Challenge from route parameter
        if ($request->route('challenge')) {
            $challengeParam = $request->route('challenge');
            $challenge = $challengeParam instanceof Challenge ? $challengeParam : Challenge::find($challengeParam);
            return $challenge?->group;
        }

        // Event from route parameter
        if ($request->route('event')) {
            $eventParam = $request->route('event');
            $event = $eventParam instanceof GroupEvent ? $eventParam : GroupEvent::find($eventParam);
            return $event?->group;
        }

        return null;
    }
}
