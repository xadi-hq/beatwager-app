<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform-Agnostic Messages
    |--------------------------------------------------------------------------
    |
    | All bot messages are centralized here in a platform-agnostic format.
    | No HTML, no Markdown, no platform-specific syntax.
    | Variables use {curly_braces} and will be replaced at runtime.
    |
    | These templates work for Telegram, Slack, Discord, or any future platform.
    |
    */

    'wager' => [
        'announced' => "🎯 New Wager Created!\n\nQuestion: {title}\n\nDescription: {description}\nType: {type}\nStake: {stake} points\nDeadline: {deadline}\n\nClick a button below to place your wager!",
        
        'joined' => "✅ Wager placed successfully!",
        
        'settled' => "🏁 Wager Settled!\n\nQuestion: {title}\nOutcome: {outcome}\n\n{note}",
        
        'reminder' => "⏰ Settlement Reminder\n\nWager: {title}\n\nThis wager passed its deadline and is waiting to be settled.\n\nClick the button below to view details and settle:",
    ],

    'winners' => [
        'header' => "\nWinners:\n",
        'single' => "✅ {name} won {points} points\n",
        'none' => "No winners for this wager.\n",
    ],

    'progress' => [
        'dm_title' => "📊 View Wager Progress\n\n",
        'dm_body' => "Wager: {title}\n\nClick the button below to view full details, stats, and settlement options:",
    ],

    'errors' => [
        'wager_not_found' => "❌ Wager not found",
        'already_joined' => "❌ You've already joined this wager",
        'insufficient_points' => "❌ Insufficient points (need {required}, have {balance})",
        'deadline_passed' => "❌ Deadline has passed",
    ],

    'buttons' => [
        'yes' => '✅ Yes',
        'no' => '❌ No',
        'view_progress' => '📊 View Progress',
        'open_wager_page' => '🔗 Open Wager Page',
        'settle_wager' => '⚖️ Settle Wager',
    ],
];