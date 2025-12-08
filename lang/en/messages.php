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

        'reminder_dm' => [
            'intent' => 'Gentle reminder to wager creator 1 hour after expected settlement',
            'required_fields' => ['title'],
            'fallback_template' => "👋 Hey! The result for \"{title}\" should be known by now.\n\nWhen you're ready, settle it using the button below:",
            'tone_hints' => ['gentle', 'respectful', 'helpful'],
            'max_words' => 30,
        ],

        'reminder_group' => [
            'intent' => 'Playful group reminder to settle wager, escalating with days_waiting',
            'required_fields' => ['title', 'creator', 'days_waiting', 'participant_count', 'escalation_level'],
            'fallback_template' => "⏰ Still waiting on \"{title}\" - let's help {creator} out! Anyone know the answer?",
            'tone_hints' => ['playful', 'collaborative', 'escalating'],
            'personality_notes' => 'Use escalation_level to adjust tone: gentle (Day 1: "Let\'s help them out"), reminder (Days 2-3: "Still waiting"), urgent (Days 4-7: "Getting old"), critical (Day 8+: "Really needs settling")',
            'examples' => [
                'gentle: "Still waiting on \"{title}\" 🤔 Let\'s help {creator} out - anyone know the answer?"',
                'reminder: "Day {days_waiting} waiting for \"{title}\" to be settled. {creator}, need help?"',
                'urgent: "⚠️ \"{title}\" has been waiting {days_waiting} days! {participant_count} people are waiting, let\'s wrap this up!"',
                'critical: "🚨 Seriously, \"{title}\" needs settlement - it\'s been {days_waiting} days! {creator}, what\'s the holdup?"',
            ],
            'max_words' => 40,
        ],
    ],

    'event' => [
        'announced' => [
            'intent' => 'Announce a newly created group event and encourage RSVPs',
            'required_fields' => ['name', 'event_date', 'attendance_bonus'],
            'optional_fields' => ['streaks_at_risk', 'has_active_streaks', 'streak_instructions'],
            'fallback_template' => "🎉 New Event: {name}\n\n📅 When: {event_date}\n📍 Where: {location}\n💰 Bonus: +{attendance_bonus} {currency} for attending!\n\n{description}",
            'tone_hints' => ['exciting', 'inviting'],
            'personality_notes' => 'If streaks_at_risk exists, naturally mention top streaks at risk. Be conversational about streak continuation (e.g., "Will John keep his 8-event streak alive?"). Don\'t be preachy.',
            'examples' => [
                'With streaks: "🎉 Game Night this Friday! Will Sarah keep her 12-event streak going? 👀"',
                'Without streaks: "🎉 Game Night this Friday! RSVP now for +100 points!"',
            ],
            'max_words' => 50,  // Room for event details + streak mentions
        ],
        'attendance_recorded' => [
            'intent' => 'Announce attendance results and celebrate participants with streak achievements',
            'required_fields' => ['name', 'attendee_count', 'attendance_bonus'],
            'optional_fields' => ['streak_context', 'has_streaks'],
            'fallback_template' => "✅ Attendance Recorded: {name}\n\n👥 Attended ({attendee_count}): {attendees}\n💰 Each attendee received +{attendance_bonus} {currency}!",
            'tone_hints' => ['celebratory', 'congratulatory', 'enthusiastic'],
            'personality_notes' => 'If streak_context exists with top_streaks, naturally highlight them. Celebrate milestones enthusiastically (10th, 20th attendance). Mention next event for streak continuation. Be conversational, not formulaic.',
            'examples' => [
                'With streaks: "🔥 Game Night complete! John\'s on an 8-event streak now! Sarah hit the 10-event milestone! Will they keep it going at Movie Night next week?"',
                'Without streaks: "✅ Game Night was a blast! 5 people showed up and earned +100 points each!"',
            ],
            'max_words' => 60,  // Room for attendee list + streak celebrations
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

        'cancelled' => [
            'intent' => 'Announce event cancellation',
            'required_fields' => ['event_name'],
            'fallback_template' => "🚫 {event_name} has been cancelled.",
            'tone_hints' => ['informative', 'disappointing', 'matter-of-fact'],
            'max_words' => 20,
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
        'betting_closed' => [
            'intent' => 'Reveal all bets now that betting has closed, build excitement for the outcome',
            'required_fields' => ['wager_title', 'participant_count', 'stake_amount', 'currency', 'bets_summary'],
            'optional_fields' => ['expected_settlement_at'],
            'fallback_template' => "🔔 Betting is now CLOSED for {wager_title}!\n\n{participant_count} brave souls wagered {stake_amount} {currency} each.\n\n📊 The bets are in:\n{bets_summary}\n\nNow we wait for the results! 🎯",
            'tone_hints' => ['dramatic', 'suspenseful', 'exciting', 'reveal'],
            'personality_notes' => 'Build anticipation for the outcome, comment on interesting bet distributions or surprising picks',
            'examples' => [
                "🔒 Betting CLOSED on \"Ajax - Groningen\"!\n\n8 of you put your money where your mouth is:\n\n🏠 Ajax: Sarah, Mike, Tom\n🚌 Groningen: Dave, Lisa\n🤝 Draw: Alex, Chris, Emma\n\nThis is going to be SPICY! 🌶️",
                "⏰ Time's up! \"Will it rain tomorrow?\" is locked in!\n\n☀️ Team No Rain: 4 believers\n🌧️ Team Umbrella: 3 pessimists\n\nMother Nature has the final say!",
            ],
            'max_words' => 80,
        ],
    ],

    'season' => [
        'started' => [
            'intent' => 'Announce a new season starting with excitement and prize details if configured',
            'required_fields' => ['season_number', 'starting_balance', 'currency'],
            'fallback_template' => "🎉 Season {season_number} Has Begun!\n\nEveryone starts fresh with {starting_balance} {currency}!\n\n" .
                                  "{ends_at ? \"📅 Season ends: {ends_at}\n\n\" : \"\"}" .
                                  "{has_prizes ? \"🏆 Prizes This Season:\n{prizes}\n\n\" : \"\"}" .
                                  "Time to make your mark! Who will come out on top?",
            'tone_hints' => ['exciting', 'motivating', 'fresh-start'],
            'max_words' => 150,  // Room for prize details
        ],
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
        'birthday_reminder' => [
            'intent' => 'Remind the group about an upcoming birthday and encourage them to organize something',
            'required_fields' => ['member_name', 'days_until'],
            'fallback_template' => "🎂 Heads up! {member_name}'s birthday is in {days_until} days!\n\nWhen are we celebrating? 🎉",
            'tone_hints' => ['playful', 'encouraging', 'warm'],
            'personality_notes' => 'Create FOMO about missing the celebration. Encourage group to organize something together. Be conversational and fun.',
            'examples' => [
                '🎂 John\'s 40th birthday is next week! Who\'s planning the surprise party? 👀',
                '🎉 Sarah\'s birthday in 7 days! Are we doing cake? Drinks? Both? LET\'S PLAN!',
                '⏰ Heads up team - Mike\'s birthday next Tuesday! Time to organize something epic 🎊',
            ],
            'max_words' => 50,
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

        'accepted' => [
            'intent' => 'Announce a user accepting a challenge to create excitement',
            'required_fields' => ['user_name', 'challenge_title'],
            'fallback_template' => "💪 {user_name} accepted the challenge \"{challenge_title}\"! Let's see what they've got!",
            'tone_hints' => ['exciting', 'supportive', 'competitive'],
            'max_words' => 25,
        ],

        'approved' => [
            'intent' => 'Celebrate approved challenge with reward',
            'required_fields' => ['user_name', 'challenge_title', 'reward', 'currency'],
            'fallback_template' => "✅ {user_name} crushed \"{challenge_title}\" and earned {reward} {currency}! 🏆",
            'tone_hints' => ['celebratory', 'congratulatory', 'triumphant'],
            'max_words' => 30,
        ],

        'rejected' => [
            'intent' => 'Acknowledge rejected challenge submission with encouragement',
            'required_fields' => ['user_name', 'challenge_title'],
            'fallback_template' => "❌ {user_name}'s work on \"{challenge_title}\" didn't meet the mark this time. Keep pushing! 💪",
            'tone_hints' => ['supportive', 'encouraging', 'honest'],
            'max_words' => 30,
        ],

        'cancelled' => [
            'intent' => 'Announce challenge cancellation',
            'required_fields' => ['challenge_title'],
            'fallback_template' => "🚫 Challenge \"{challenge_title}\" has been cancelled.",
            'tone_hints' => ['neutral', 'informative'],
            'max_words' => 20,
        ],

        'expired' => [
            'intent' => 'Announce challenge expired without being accepted',
            'required_fields' => ['challenge_title'],
            'fallback_template' => "⏰ Challenge \"{challenge_title}\" expired - nobody stepped up in time!",
            'tone_hints' => ['missed_opportunity', 'playful_guilt'],
            'max_words' => 20,
        ],

        'deadline_missed' => [
            'intent' => 'Announce accepted challenge deadline was missed',
            'required_fields' => ['user_name', 'challenge_title'],
            'fallback_template' => "⏱️ {user_name} ran out of time on \"{challenge_title}\" - better luck next time!",
            'tone_hints' => ['disappointed', 'sympathetic', 'encouraging'],
            'max_words' => 25,
        ],
    ],

    'superchallenge' => [
        'nudge' => [
            'intent' => 'Encourage randomly selected user to create a SuperChallenge for the group',
            'required_fields' => ['group_name', 'frequency'],
            'fallback_template' => "✨ You've been chosen to create a SuperChallenge for {group_name}!\n\nSuperChallenges are collaborative - everyone who completes it gets the full prize. They happen {frequency}.\n\nClick below to create one or pass:",
            'tone_hints' => ['encouraging', 'exciting', 'opportunity'],
            'personality_notes' => 'Make them feel special for being chosen. Emphasize the collaborative nature (not competitive).',
            'max_words' => 40,
        ],

        'announced' => [
            'intent' => 'Announce a new SuperChallenge to the group and rally participation',
            'required_fields' => ['description', 'prize_per_person', 'max_participants', 'deadline_at', 'currency'],
            'optional_fields' => ['current_participants', 'evidence_guidance'],
            'fallback_template' => "🏆 New SuperChallenge!\n\n{description}\n\n💰 Prize: {prize_per_person} {currency} per person\n👥 Spots: {current_participants}/{max_participants}\n⏰ Deadline: {deadline_at}\n\nThis is collaborative - everyone who completes it wins! Click below to join:",
            'tone_hints' => ['exciting', 'rallying', 'collaborative'],
            'personality_notes' => 'Emphasize collaboration over competition. Make it sound achievable and fun. Use group personality.',
            'examples' => [
                'Fitness group: "💪 Time to get moving together! Who\'s ready to crush this?"',
                'Friends group: "🎯 Let\'s make this happen as a crew!"',
                'Professional: "🚀 Collaborative goal time - let\'s all level up together."',
            ],
            'max_words' => 45,
        ],

        'accepted' => [
            'intent' => 'Celebrate user accepting the SuperChallenge and encourage others to join',
            'required_fields' => ['user_name', 'description', 'current_participants', 'max_participants'],
            'optional_fields' => ['prize_per_person', 'currency'],
            'fallback_template' => "💪 {user_name} joined the SuperChallenge!\n\nChallenge: {description}\n\n👥 {current_participants}/{max_participants} spots filled\n\nWho's next?",
            'tone_hints' => ['celebratory', 'FOMO', 'encouraging'],
            'personality_notes' => 'Create excitement and slight urgency as spots fill up. Celebrate the joiner.',
            'max_words' => 30,
        ],

        'completion_claimed' => [
            'intent' => 'Notify creator that participant claims completion and needs validation',
            'required_fields' => ['user_name', 'description', 'completed_at'],
            'optional_fields' => ['prize_per_person', 'currency'],
            'fallback_template' => "🎯 Completion to Validate\n\n{user_name} claims they completed:\n\"{description}\"\n\nCompleted at: {completed_at}\n\nCheck the evidence in the chat and click below to approve or reject.\n\nYou'll get +25 {currency} for validating!",
            'tone_hints' => ['professional', 'clear', 'action_required'],
            'personality_notes' => 'Be clear about what they need to do. Mention the validation bonus as incentive.',
            'max_words' => 50,
        ],

        'validated_approved' => [
            'intent' => 'Announce approved completion with celebration and point award',
            'required_fields' => ['user_name', 'description', 'prize_per_person', 'currency'],
            'fallback_template' => "✅ {user_name} completed the SuperChallenge!\n\n\"{description}\"\n\n💰 +{prize_per_person} {currency} awarded!\n\nGreat work! 🎉",
            'tone_hints' => ['celebratory', 'rewarding', 'positive'],
            'personality_notes' => 'Big celebration! They earned it. Adapt tone to group personality.',
            'examples' => [
                'Casual: "🔥 BOOM! {user_name} crushed it and earned {prize_per_person} points!"',
                'Professional: "✅ Excellent work, {user_name}! Challenge completed. +{prize_per_person} points."',
            ],
            'max_words' => 35,
        ],

        'validated_rejected' => [
            'intent' => 'Diplomatically announce rejected completion without being harsh',
            'required_fields' => ['user_name', 'description'],
            'fallback_template' => "⚠️ {user_name}'s completion for \"{description}\" wasn't approved.\n\nThe creator reviewed the evidence and it didn't meet the requirements. No points awarded.",
            'tone_hints' => ['diplomatic', 'factual', 'constructive'],
            'personality_notes' => 'Don\'t be harsh. State facts clearly. Suggest they can try again or discuss with creator.',
            'max_words' => 35,
        ],

        'auto_validated' => [
            'intent' => 'Announce auto-approval after 48h timeout with explanation',
            'required_fields' => ['user_name', 'description', 'prize_per_person', 'currency', 'hours_waited'],
            'fallback_template' => "⏰ Auto-Approved!\n\n{user_name} completed \"{description}\"\n\nThe creator didn't validate within {hours_waited} hours, so it was automatically approved.\n\n💰 +{prize_per_person} {currency} awarded!",
            'tone_hints' => ['fair', 'systematic', 'positive'],
            'personality_notes' => 'Explain the timeout clearly. Make it feel fair and systematic, not like creator failed.',
            'max_words' => 40,
        ],
    ],

    'elimination' => [
        'announced' => [
            'intent' => 'Announce a new elimination challenge with survival drama and buy-in info',
            'required_fields' => ['challenge_name', 'elimination_trigger', 'mode', 'pot', 'buy_in', 'currency'],
            'optional_fields' => ['deadline', 'tap_in_deadline', 'min_participants', 'creator_name'],
            'fallback_template' => "🎯 New Elimination Challenge!\n\n\"{challenge_name}\"\n\n🚫 Trigger: {elimination_trigger}\n💰 Pot: {pot} {currency}\n🎟️ Buy-in: {buy_in} {currency}\n\n{mode_description}\n\nWho's brave enough to tap in?",
            'tone_hints' => ['dramatic', 'competitive', 'survivor_vibes'],
            'personality_notes' => 'Create tension and excitement. Make it feel like a survival game. Use mode to set expectations (last man standing = intense, deadline = strategic).',
            'examples' => [
                'Last man standing: "🎯 The Wham! Challenge begins! Avoid hearing \'Last Christmas\' or tap out. Last survivor takes 500 points! 💀"',
                'Deadline mode: "⏰ 30-day no fast food challenge! Survivors at deadline split 1000 points. Are you in?"',
            ],
            'max_words' => 60,
        ],

        'tapped_in' => [
            'intent' => 'Celebrate someone joining the elimination challenge with competitive energy',
            'required_fields' => ['user_name', 'challenge_name', 'participant_count', 'buy_in', 'currency'],
            'optional_fields' => ['pot_per_survivor'],
            'fallback_template' => "💪 {user_name} tapped in to \"{challenge_name}\"!\n\n👥 {participant_count} survivors so far\n💰 Current pot share: {pot_per_survivor} {currency} each\n\nWho else dares to join?",
            'tone_hints' => ['competitive', 'FOMO', 'exciting'],
            'personality_notes' => 'Make others feel like they\'re missing out. Create hype around growing participant count.',
            'max_words' => 35,
        ],

        'activated' => [
            'intent' => 'Announce challenge officially starting after minimum participants reached',
            'required_fields' => ['challenge_name', 'participant_count', 'pot', 'currency'],
            'fallback_template' => "🚀 It's ON! \"{challenge_name}\" is now LIVE!\n\n👥 {participant_count} brave souls are in\n💰 Total pot: {pot} {currency}\n\nLet the survival games begin! 🎯",
            'tone_hints' => ['exciting', 'dramatic', 'game_on'],
            'personality_notes' => 'This is the moment of activation. Build hype. Make it feel like the games are starting.',
            'max_words' => 35,
        ],

        'tapped_out' => [
            'intent' => 'Announce elimination with drama and updated survivor count',
            'required_fields' => ['user_name', 'challenge_name', 'days_survived', 'survivor_count', 'eliminated_count'],
            'optional_fields' => ['elimination_note', 'pot_per_survivor', 'currency'],
            'fallback_template' => "💀 {user_name} is OUT!\n\n{elimination_note_section}Survived: {days_survived} days\n\n👥 {survivor_count} survivors remain\n💰 Pot per survivor: {pot_per_survivor} {currency}",
            'tone_hints' => ['dramatic', 'fallen_soldier', 'suspenseful'],
            'personality_notes' => 'Honor their attempt while building drama. If elimination_note exists, use it to tell the story of their downfall. Update stakes for remaining survivors.',
            'examples' => [
                'With note: "💀 Mike is OUT! He heard \'Last Christmas\' at the mall food court. 5 days survived. 4 survivors remain fighting for 200 points each!"',
                'Without note: "💀 Sarah tapped out after 12 brave days. 3 survivors left - stakes just got higher!"',
            ],
            'max_words' => 45,
        ],

        'milestone' => [
            'intent' => 'Announce milestone moments to build drama and engagement',
            'required_fields' => ['challenge_name', 'milestone', 'survivor_count', 'pot_per_survivor', 'currency'],
            'optional_fields' => ['survivor_names', 'eliminated_count'],
            'fallback_template' => "{milestone_message}\n\n👥 {survivor_count} still standing\n💰 Pot per survivor: {pot_per_survivor} {currency}",
            'tone_hints' => ['dramatic', 'suspenseful', 'escalating'],
            'personality_notes' => 'Milestones are "half_eliminated" and "final_two". Build tension. For final two, create head-to-head rivalry feel.',
            'examples' => [
                'half_eliminated: "⚡ HALF THE FIELD IS DOWN! Only 5 survivors remain in the Wham! Challenge. Stakes are doubling! 🎯"',
                'final_two: "🔥 FINAL TWO! Sarah vs Mike - one will walk away with 500 points, one walks away with nothing! 💀"',
            ],
            'max_words' => 40,
        ],

        'countdown' => [
            'intent' => 'Build urgency with time-based countdown messages',
            'required_fields' => ['challenge_name', 'time_remaining', 'survivor_count', 'pot_per_survivor', 'currency'],
            'optional_fields' => ['survivor_names', 'social_engineering_prompt'],
            'fallback_template' => "⏰ {time_remaining} remaining!\n\n\"{challenge_name}\" ends soon\n\n👥 {survivor_count} survivors\n💰 Each takes home: {pot_per_survivor} {currency}\n\n{social_prompt}",
            'tone_hints' => ['urgent', 'suspenseful', 'strategic'],
            'personality_notes' => 'Countdown triggers: 7d, 48h, 24h, 6h, 1h. Include social engineering - subtly encourage survivors to eliminate each other. More aggressive as deadline approaches.',
            'examples' => [
                '7 days: "⏰ One week left! 8 survivors holding strong. Anyone feeling tempted to slip up? 👀"',
                '48 hours: "⏰ 48 hours to go! 5 survivors, 400 points each. The pressure is ON. Who will crack first? 😈"',
                '24 hours: "🔥 FINAL 24 HOURS! 4 survivors splitting 500 points. One slip-up changes everything! Time to test your willpower..."',
                '6 hours: "⚡ 6 HOURS LEFT! 3 survivors about to split 600 points each. SO close to victory... or are they? 😏"',
                '1 hour: "🚨 FINAL HOUR! If you\'re still in, you\'ve basically won... unless someone cracks in the next 60 minutes! 👀"',
            ],
            'max_words' => 50,
        ],

        'resolved' => [
            'intent' => 'Announce challenge completion with winner celebration',
            'required_fields' => ['challenge_name', 'mode', 'survivor_count', 'prize_per_survivor', 'currency'],
            'optional_fields' => ['survivor_names', 'total_participants', 'duration_days'],
            'fallback_template' => "🏆 {challenge_name} is COMPLETE!\n\n{winner_announcement}\n\n🎯 Duration: {duration_days} days\n👥 Survivors: {survivor_count}/{total_participants}\n💰 Prize each: {prize_per_survivor} {currency}\n\nCongratulations to the champions!",
            'tone_hints' => ['triumphant', 'celebratory', 'dramatic_conclusion'],
            'personality_notes' => 'For last_man_standing, celebrate the single winner heroically. For deadline mode, celebrate all survivors equally. Use names if available.',
            'examples' => [
                'last_man_standing: "👑 THE CHAMPION! Sarah outlasted everyone in the Wham! Challenge and takes home 500 points! 🏆"',
                'deadline: "🎉 SURVIVORS WIN! Mike, Sarah, and Tom made it 30 days without fast food! Each takes home 333 points! 💪"',
            ],
            'max_words' => 60,
        ],

        'cancelled' => [
            'intent' => 'Announce challenge cancellation with refund information',
            'required_fields' => ['challenge_name'],
            'optional_fields' => ['cancelled_by', 'reason', 'refund_amount', 'currency'],
            'fallback_template' => "🚫 \"{challenge_name}\" has been cancelled.\n\n{reason_section}All participants have been refunded their buy-in.",
            'tone_hints' => ['neutral', 'informative', 'reassuring'],
            'personality_notes' => 'Be clear and reassuring about refunds. Common reasons: not enough participants, creator cancelled.',
            'max_words' => 30,
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
