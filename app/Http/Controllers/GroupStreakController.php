<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EventStreakConfig;
use App\Models\Group;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupStreakController extends Controller
{
    /**
     * Get streak configuration for a group
     */
    public function show(Group $group)
    {
        $user = Auth::user();

        // Verify user is member of group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'Not a member of this group');
        }

        $config = EventStreakConfig::where('group_id', $group->id)->first();

        if (!$config) {
            // Create default config if doesn't exist
            $config = EventStreakConfig::create([
                'group_id' => $group->id,
                'enabled' => false,
                'multiplier_tiers' => [
                    ['min' => 1, 'max' => 3, 'multiplier' => 1.0],
                    ['min' => 4, 'max' => 6, 'multiplier' => 1.2],
                    ['min' => 7, 'max' => 9, 'multiplier' => 1.4],
                    ['min' => 10, 'max' => 19, 'multiplier' => 1.5],
                    ['min' => 20, 'max' => null, 'multiplier' => 2.0],
                ],
            ]);
        }

        return response()->json($config);
    }

    /**
     * Update streak configuration for a group
     */
    public function store(Request $request, Group $group)
    {
        $user = Auth::user();

        // Verify user is member of group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            abort(403, 'Not a member of this group');
        }

        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'multiplier_tiers' => 'required|array|min:1',
            'multiplier_tiers.*.min' => 'required|integer|min:1',
            'multiplier_tiers.*.max' => 'nullable|integer|min:1',
            'multiplier_tiers.*.multiplier' => 'required|numeric|min:1.0|max:5.0',
        ]);

        // Validate tier logic: each tier's min should be > previous tier's max
        $tiers = $validated['multiplier_tiers'];
        usort($tiers, fn($a, $b) => $a['min'] <=> $b['min']);

        for ($i = 0; $i < count($tiers) - 1; $i++) {
            $current = $tiers[$i];
            $next = $tiers[$i + 1];

            if ($current['max'] !== null && $next['min'] <= $current['max']) {
                return back()->withErrors([
                    'error' => 'Tiers must not overlap. Check tier boundaries.',
                ]);
            }
        }

        $config = EventStreakConfig::where('group_id', $group->id)->first();

        $oldConfig = $config ? [
            'enabled' => $config->enabled,
            'multiplier_tiers' => $config->multiplier_tiers,
        ] : null;

        if ($config) {
            $config->update($validated);
        } else {
            $config = EventStreakConfig::create([
                'group_id' => $group->id,
                ...$validated,
            ]);
        }

        // Audit log
        AuditService::log(
            action: 'streak_config.updated',
            auditable: $group,
            metadata: [
                'old_config' => $oldConfig,
                'new_config' => [
                    'enabled' => $config->enabled,
                    'multiplier_tiers' => $config->multiplier_tiers,
                ],
                'config_id' => $config->id,
            ],
            actor: $user
        );

        // Return back for Inertia (no redirect needed, just success)
        return back();
    }
}
