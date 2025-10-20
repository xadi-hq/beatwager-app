<?php

return [
    // Structured message metadata with fallback templates
    'wager' => [
        'announced' => [
            'intent' => 'Announce a newly created wager and drive participation',
            'required_fields' => ['title', 'type', 'stake', 'betting_closes_at'],
            'fallback_template' => "🎯 New Wager Created!\n\nQuestion: {title}\n\nDescription: {description}\nType: {type}\nStake: {stake} points\nBetting Closes: {betting_closes_at}\nResult Expected: {expected_settlement_at}\n\nClick a button below to place your wager!",
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
            'required_fields' => ['title', 'outcome', 'winners', 'currency'],
            'optional_fields' => ['note', 'grudge_context'],
            'fallback_template' => "🏁 Wager Settled!\n\nQuestion: {title}\nOutcome: {outcome}\nWinners: {winners}\n\n{note}",
            'tone_hints' => ['dramatic'],
            'personality_notes' => 'Use grudge_context for rivalries. Add trash talk for streaks.',
            'examples' => [
                'With grudge: "🔥 Sarah wins AGAIN! That\'s 3 in a row against John. When will you learn, John?"',
                'Without grudge: "🎯 Marathon Bet settled! Sarah takes home 150 points."',
            ],
            'max_words' => 50,  // Longer for winners list and celebration
        ],

        'reminder' => [
            'intent' => 'Remind users to settle a wager past its settlement date',
            'required_fields' => ['title'],
            'fallback_template' => "⏰ Settlement Reminder\n\nWager: {title}\n\nThis wager is ready to be settled.\n\nClick the button below to view details and settle:",
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

        'rsvp_going' => [
            'intent' => 'Celebrate someone joining the event - party time!',
            'required_fields' => ['user_name', 'event_name'],
            'fallback_template' => "🎉 {user_name} is coming to {event_name}!",
            'tone_hints' => ['excited', 'celebratory', 'party'],
            'max_words' => 20,
        ],

        'rsvp_maybe' => [
            'intent' => 'Playfully tease someone who is undecided',
            'required_fields' => ['user_name', 'event_name'],
            'fallback_template' => "🤔 {user_name} might come to {event_name}... or might not 🤷",
            'tone_hints' => ['playful', 'teasing', 'lighthearted'],
            'max_words' => 20,
        ],

        'rsvp_not_going' => [
            'intent' => 'Express disappointment that someone cannot make it',
            'required_fields' => ['user_name', 'event_name'],
            'fallback_template' => "😢 {user_name} can't make it to {event_name}",
            'tone_hints' => ['disappointed', 'dramatic', 'guilt-trip'],
            'max_words' => 20,
        ],

        // RSVP Changes (when someone updates their response)
        'rsvp_changed_to_going' => [
            'intent' => 'Celebrate someone changing their mind to attend - redemption!',
            'required_fields' => ['user_name', 'event_name', 'previous_response'],
            'fallback_template' => "🎉 {user_name} changed their mind and is now coming to {event_name}!",
            'tone_hints' => ['excited', 'redemption', 'celebratory'],
            'max_words' => 25,
        ],

        'rsvp_changed_to_maybe' => [
            'intent' => 'Tease someone who downgraded or became uncertain',
            'required_fields' => ['user_name', 'event_name', 'previous_response'],
            'fallback_template' => "🤔 {user_name} is now unsure about {event_name}",
            'tone_hints' => ['playful', 'teasing', 'uncertain'],
            'max_words' => 25,
        ],

        'rsvp_changed_to_not_going' => [
            'intent' => 'Express extra disappointment that someone changed to not attending',
            'required_fields' => ['user_name', 'event_name', 'previous_response'],
            'fallback_template' => "😭 {user_name} changed their mind and can't make {event_name} anymore",
            'tone_hints' => ['disappointed', 'dramatic', 'betrayal'],
            'max_words' => 25,
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
        'betting_closed' => "❌ Betting is closed",
    ],

    'activity' => [
        'revival' => [
            'intent' => 'Re-engage an inactive group with humor and encourage activity',
            'required_fields' => ['days_inactive'],
            'fallback_template' => "😴 Haven't heard from you in {days_inactive} days!\n\nTime to wake up and place some wagers! Who's in?",
            'tone_hints' => ['playful', 'encouraging', 'energetic'],
            'max_words' => 40,  // Room for creative revival messaging
        ],
    ],

    'engagement' => [
        'stale_wager' => [
            'intent' => 'Encourage participation in a wager with low or no participants',
            'required_fields' => ['wager_title', 'hours_since_created', 'participant_count', 'stake_amount', 'currency'],
            'optional_fields' => ['betting_closes_at', 'deadline_hours'],
            'fallback_template' => "👀 {wager_title} needs some action!\n\n{hours_since_created} hours and only {participant_count} participant(s)?\n\nCome on, who's brave enough to wager {stake_amount} {currency}?",
            'tone_hints' => ['playful', 'encouraging', 'FOMO', 'competitive'],
            'personality_notes' => 'Call out inactivity, create urgency, mention low stakes for easy bets',
            'examples' => [
                '🦗 Cricket sounds... "Marathon Bet" has been sitting here for 26 hours with ZERO participants. Is everyone scared or just broke?',
                '⏰ Time is ticking! "Who wins the election?" closes in 8 hours and only Sarah had the guts to join. Who else is in?',
                '💰 Low risk, high bragging rights! Only 10 points to join "Movie night attendance" - what are you waiting for?',
            ],
            'max_words' => 40,
        ],
    ],

    'season' => [
        'ended' => [
            'intent' => 'Announce season ending with dramatic recap and celebrate the winner',
            'required_fields' => ['season_number', 'winner_name', 'winner_points', 'duration_days'],
            'fallback_template' => "🏆 Season {season_number} Has Ended!\n\nDuration: {duration_days} days\nTotal Wagers: {total_wagers}\n\n👑 Champion: {winner_name} with {winner_points} {currency}!\n\nTop 3:\n{top_3}\n\n🎯 Highlights:\n{highlights}\n\nWhat a season! Ready for the next one?",
            'tone_hints' => ['dramatic', 'celebratory', 'nostalgic'],
            'max_words' => 200,  // Long recap with highlights
        ],
    ],

    'scheduled' => [
        'custom' => [
            'intent' => 'Send a scheduled message for special occasions or custom dates',
            'required_fields' => ['title', 'scheduled_date'],
            'fallback_template' => "📅 {title}\n\n{message_template}",
            'tone_hints' => ['celebratory', 'warm', 'engaging'],
            'max_words' => 100,
        ],
        'holiday' => [
            'intent' => 'Celebrate a holiday with the group',
            'required_fields' => ['holiday_name'],
            'fallback_template' => "🎉 Happy {holiday_name}!\n\nHope everyone is enjoying the day!",
            'tone_hints' => ['festive', 'warm', 'inclusive'],
            'max_words' => 80,
        ],
        'birthday' => [
            'intent' => 'Celebrate a member\'s birthday',
            'required_fields' => ['member_name'],
            'fallback_template' => "🎂 Happy Birthday {member_name}!\n\nWishing you a fantastic day!",
            'tone_hints' => ['celebratory', 'warm', 'fun'],
            'max_words' => 60,
        ],
    ],

    'challenge' => [
        'announced' => [
            'intent' => 'Announce a newly created challenge and encourage participation',
            'required_fields' => ['title', 'description', 'reward', 'deadline_at'],
            'fallback_template' => "🚀 New Challenge Created!\n\nChallenge: {title}\n\nDescription: {description}\nReward: {reward} points\nDeadline: {deadline_at}\n\nClick the button below to accept this challenge!",
            'tone_hints' => ['exciting', 'challenging', 'motivating'],
            'max_words' => 35,  // Room for challenge details
        ],

        'joined' => [
            'intent' => 'Announce a user accepting a challenge to create excitement',
            'required_fields' => ['user_name', 'challenge_title'],
            'fallback_template' => "{user_name} accepted the challenge \"{challenge_title}\"! Let's see what they've got! 💪",
            'tone_hints' => ['exciting', 'supportive', 'competitive'],
            'max_words' => 25,
        ],

        'submitted' => [
            'intent' => 'Announce challenge submission and build anticipation for evaluation',
            'required_fields' => ['user_name', 'challenge_title'],
            'fallback_template' => "📥 {user_name} submitted their work for \"{challenge_title}\"! Time to see if they crushed it! 🎯",
            'tone_hints' => ['anticipation', 'exciting', 'supportive'],
            'max_words' => 25,
        ],

        'evaluated' => [
            'intent' => 'Announce challenge evaluation results with outcome',
            'required_fields' => ['user_name', 'challenge_title', 'outcome', 'reward'],
            'fallback_template' => "🏆 Challenge Evaluated!\n\nChallenge: {challenge_title}\nParticipant: {user_name}\nOutcome: {outcome}\n\n{note}",
            'tone_hints' => ['dramatic', 'celebratory'],
            'max_words' => 50,
        ],

        'completed' => [
            'intent' => 'Celebrate successful challenge completion',
            'required_fields' => ['user_name', 'challenge_title', 'reward'],
            'fallback_template' => "🎉 Challenge Completed!\n\n{user_name} successfully completed \"{challenge_title}\" and earned {reward} points! 💪",
            'tone_hints' => ['celebratory', 'congratulatory', 'triumphant'],
            'max_words' => 30,
        ],

        'failed' => [
            'intent' => 'Acknowledge challenge attempt with encouragement',
            'required_fields' => ['user_name', 'challenge_title'],
            'fallback_template' => "😅 {user_name} didn't quite nail \"{challenge_title}\" this time, but hey - you miss 100% of the shots you don't take! 🎯",
            'tone_hints' => ['supportive', 'encouraging', 'lighthearted'],
            'max_words' => 30,
        ],

        'reminder' => [
            'intent' => 'Remind users about approaching challenge deadline',
            'required_fields' => ['challenge_title', 'deadline_at'],
            'fallback_template' => "⏰ Challenge Reminder\n\nChallenge: {challenge_title}\nDeadline: {deadline_at}\n\nTime is running out! Click below to view details:",
            'tone_hints' => ['urgent', 'motivating'],
            'max_words' => 25,
        ],
    ],

    'buttons' => [
        'yes' => '✅ Yes',
        'no' => '❌ No',
        'view_progress' => '📊 View Progress',
        'open_wager_page' => '🔗 Open Wager Page',
        'settle_wager' => '⚖️ Settle Wager',
        'accept_challenge' => '🚀 Accept Challenge',
        'view_challenge' => '🎯 View Challenge',
        'submit_work' => '📤 Submit Work',
        'evaluate_challenge' => '⚖️ Evaluate',
    ],
];
