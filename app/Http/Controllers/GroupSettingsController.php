<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupSettingsController extends Controller
{
    /**
     * Update group settings
     */
    public function update(Request $request, Group $group)
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        $validated = $request->validate([
            'points_currency_name' => 'sometimes|string|max:50',
            'description' => 'sometimes|string|max:500|nullable',
            'notification_preferences' => 'sometimes|array',
            'notification_preferences.birthday_reminders' => 'boolean',
            'notification_preferences.event_reminders' => 'boolean',
            'notification_preferences.wager_reminders' => 'boolean',
            'notification_preferences.weekly_summaries' => 'boolean',
            'bot_tone' => 'sometimes|string|max:1000|nullable',
            'llm_api_key' => 'sometimes|string|max:500|nullable',
        ]);

        $group->update($validated);

        return redirect()->back()->with('success', 'Group settings updated successfully');
    }
}
