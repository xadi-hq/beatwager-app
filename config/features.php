<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Control feature availability across the application. Features can be
    | toggled on/off via environment variables without code changes.
    |
    */

    /**
     * Activity Tracking
     *
     * Tracks group-level activity and sends revival messages to inactive groups.
     *
     * Requirements:
     * - Telegram bot must have access to all messages (not just commands)
     * - Redis recommended for optimal performance
     *
     * When enabled:
     * - Updates group.last_activity_at on every message
     * - Scheduled job checks for inactive groups daily
     * - Sends LLM-powered revival messages after threshold
     *
     * @see app/Console/Commands/CheckGroupActivity.php
     * @see app/Services/MessageService::revivalMessage()
     */
    'activity_tracking' => env('FEATURE_ACTIVITY_TRACKING', false),

];
