<?php

return [
    // Structured message metadata with fallback templates
    'wager' => [
        'announced' => [
            'intent' => 'Announce a newly created wager and drive participation',
            'required_fields' => ['title', 'type', 'stake', 'deadline'],
            'fallback_template' => "🎯 New Wager Created!\n\nQuestion: {title}\n\nDescription: {description}\nType: {type}\nStake: {stake} points\nDeadline: {deadline}\n\nClick a button below to place your wager!",
            'tone_hints' => ['exciting', 'call_to_action'],
            'max_words' => 30,  // Short and punchy announcements
        ],

        'joined' => [
            'intent' => 'Announce a user joining a wager to create FOMO and engagement (DO NOT reveal their answer - blind wagers!)',
            'required_fields' => ['user_name', 'wager_title', 'points_wagered', 'currency'],
            'fallback_template' => "{user_name} joined \"{wager_title}\" with {points_wagered} {currency}!",
            'tone_hints' => ['exciting', 'engaging', 'FOMO'],
            'max_words' => 25,  // Room for personality and triggers
        ],

        'settled' => [
            'intent' => 'Announce a wager settlement with outcome and winners',
            'required_fields' => ['title', 'outcome'],
            'fallback_template' => "🏁 Wager Settled!\n\nQuestion: {title}\nOutcome: {outcome}\n\n{note}",
            'tone_hints' => ['dramatic'],
            'max_words' => 50,  // Longer for winners list and celebration
        ],

        'reminder' => [
            'intent' => 'Remind users to settle a wager past its deadline',
            'required_fields' => ['title'],
            'fallback_template' => "⏰ Settlement Reminder\n\nWager: {title}\n\nThis wager passed its deadline and is waiting to be settled.\n\nClick the button below to view details and settle:",
            'tone_hints' => ['urgent'],
            'max_words' => 25,  // Brief but urgent
        ],
    ],

    'event' => [
        'announced' => [
            'intent' => 'Announce a newly created group event and encourage RSVPs',
            'required_fields' => ['name', 'event_date', 'attendance_bonus'],
            'fallback_template' => "🎉 New Event: {name}\n\n📅 When: {event_date}\n📍 Where: {location}\n💰 Bonus: +{attendance_bonus} {currency} for attending!\n\n{description}",
            'tone_hints' => ['exciting', 'inviting'],
            'max_words' => 40,  // Room for event details
        ],
        'attendance_recorded' => [
            'intent' => 'Announce attendance results and celebrate participants',
            'required_fields' => ['name', 'attendee_count', 'attendance_bonus'],
            'fallback_template' => "✅ Attendance Recorded: {name}\n\n👥 Attended ({attendee_count}): {attendees}\n💰 Each attendee received +{attendance_bonus} {currency}!",
            'tone_hints' => ['celebratory', 'congratulatory'],
            'max_words' => 50,  // Room for attendee list
        ],
    ],

    'summary' => [
        'year_review' => [
            'intent' => 'Provide comprehensive year-end review with all member stats and highlights',
            'required_fields' => ['year', 'members', 'total_wagers'],
            'fallback_template' => "📊 {year} Year in Review\n\nTotal Wagers: {total_wagers}\n\nMember Stats:\n{member_stats}",
            'tone_hints' => ['celebratory', 'comprehensive'],
            'max_words' => 200,  // Long-form content for comprehensive reviews
        ],
        'monthly_recap' => [
            'intent' => 'Monthly activity summary with key highlights',
            'required_fields' => ['month', 'highlights'],
            'fallback_template' => "📅 {month} Monthly Recap\n\n{highlights}",
            'tone_hints' => ['informative', 'engaging'],
            'max_words' => 100,  // Medium length for monthly summaries
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
