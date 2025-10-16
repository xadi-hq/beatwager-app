<?php

return [
    // Structured message metadata with fallback templates
    'wager' => [
        'announced' => [
            'intent' => 'Announce a newly created wager and drive participation',
            'required_fields' => ['title', 'type', 'stake', 'deadline'],
            'fallback_template' => "🎯 New Wager Created!\n\nQuestion: {title}\n\nDescription: {description}\nType: {type}\nStake: {stake} points\nDeadline: {deadline}\n\nClick a button below to place your wager!",
            'tone_hints' => ['exciting', 'call_to_action'],
        ],

        'joined' => [
            'intent' => 'Confirm that a user successfully joined a wager',
            'required_fields' => [],
            'fallback_template' => "✅ Wager placed successfully!",
            'tone_hints' => ['neutral'],
        ],

        'settled' => [
            'intent' => 'Announce a wager settlement with outcome and winners',
            'required_fields' => ['title', 'outcome'],
            'fallback_template' => "🏁 Wager Settled!\n\nQuestion: {title}\nOutcome: {outcome}\n\n{note}",
            'tone_hints' => ['dramatic'],
        ],

        'reminder' => [
            'intent' => 'Remind users to settle a wager past its deadline',
            'required_fields' => ['title'],
            'fallback_template' => "⏰ Settlement Reminder\n\nWager: {title}\n\nThis wager passed its deadline and is waiting to be settled.\n\nClick the button below to view details and settle:",
            'tone_hints' => ['urgent'],
        ],
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
