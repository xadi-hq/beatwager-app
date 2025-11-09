<?php

declare(strict_types=1);

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
            'timezone' => 'sometimes|string|timezone',
            'language' => 'sometimes|string|in:en,nl,es,fr,de,it,pt',
            'description' => 'sometimes|string|max:500|nullable',
            'group_type' => 'sometimes|string|in:friends,colleagues,family',
            'notification_preferences' => 'sometimes|array',
            'notification_preferences.birthday_reminders' => 'boolean',
            'notification_preferences.event_reminders' => 'boolean',
            'notification_preferences.wager_reminders' => 'boolean',
            'notification_preferences.weekly_summaries' => 'boolean',
            'bot_tone' => 'sometimes|string|max:1000|nullable',
            'llm_provider' => 'sometimes|string|in:openai,anthropic,requesty',
            'llm_api_key' => 'sometimes|string|max:500|nullable',
            'allow_nsfw' => 'sometimes|boolean',
            'superchallenge_frequency' => 'sometimes|string|in:off,weekly,monthly,quarterly',
        ]);

        // Preserve existing LLM API key if field is empty (user didn't provide new key)
        if (isset($validated['llm_api_key']) && empty($validated['llm_api_key'])) {
            unset($validated['llm_api_key']);
        }

        $group->update($validated);

        return redirect()->back()->with('success', 'Group settings updated successfully');
    }
}
