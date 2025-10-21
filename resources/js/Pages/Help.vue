<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <Head title="Help - BeatWager" />

        <div class="max-w-4xl mx-auto py-8 px-4">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    📖 BeatWager Help
                </h1>
                <p class="text-gray-600">
                    Everything you need to know about creating and participating in wagers
                </p>
            </div>

            <!-- Getting Started -->
            <section class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Getting Started</h2>
                <p class="text-gray-700 mb-4">
                    BeatWager is a social betting platform for Telegram groups. Create wagers with your friends,
                    place bets with points, and compete for bragging rights!
                </p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">Quick Start:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-blue-800">
                        <li>Add the bot to your Telegram group</li>
                        <li>Use <code class="bg-blue-100 px-2 py-0.5 rounded">/newwager</code> to create a wager</li>
                        <li>Friends click the buttons to join and make their predictions</li>
                        <li>When the event concludes, settle the wager with the outcome</li>
                        <li>Winners split the pot proportionally!</li>
                    </ol>
                </div>
            </section>

            <!-- Commands -->
            <section class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Bot Commands</h2>

                <h3 class="font-semibold text-gray-800 mb-3 mt-4">Wagers</h3>
                <div class="space-y-4 mb-6">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/newwager</h4>
                        <p class="text-gray-600 text-sm">
                            Create a new wager in your group. Click the link to set up the wager type, question, options, stake amount, and deadline.
                        </p>
                    </div>

                    <div class="border-l-4 border-blue-400 pl-4">
                        <h4 class="font-semibold text-gray-900">/wagers</h4>
                        <p class="text-gray-600 text-sm">
                            View the top 3 most recent open wagers in this group.
                        </p>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-800 mb-3 mt-4">Events</h3>
                <div class="space-y-4 mb-6">
                    <div class="border-l-4 border-orange-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/newevent</h4>
                        <p class="text-gray-600 text-sm">
                            Create a group event with attendance tracking. Organize meetups and reward participants with points bonuses.
                        </p>
                    </div>

                    <div class="border-l-4 border-orange-400 pl-4">
                        <h4 class="font-semibold text-gray-900">/events</h4>
                        <p class="text-gray-600 text-sm">
                            View the top 3 most recent open events in this group.
                        </p>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-800 mb-3 mt-4">Challenges</h3>
                <div class="space-y-4 mb-6">
                    <div class="border-l-4 border-red-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/newchallenge</h4>
                        <p class="text-gray-600 text-sm">
                            Create a 1-on-1 challenge. You can either <strong>offer points</strong> for someone to complete a task, or <strong>request points</strong> to complete a task yourself.
                        </p>
                    </div>

                    <div class="border-l-4 border-red-400 pl-4">
                        <h4 class="font-semibold text-gray-900">/challenges</h4>
                        <p class="text-gray-600 text-sm">
                            View the top 3 most recent open challenges in this group.
                        </p>
                    </div>
                </div>

                <h3 class="font-semibold text-gray-800 mb-3 mt-4">Your Account</h3>
                <div class="space-y-4">
                    <div class="border-l-4 border-green-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/mybets</h4>
                        <p class="text-gray-600 text-sm">
                            View your active wagers. The bot will send you a DM with your top 5 upcoming wagers and a link to your full dashboard.
                        </p>
                    </div>

                    <div class="border-l-4 border-purple-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/balance</h4>
                        <p class="text-gray-600 text-sm">
                            Check your points balance. If used in a group, shows your balance in that group. If used in a DM, shows all your group balances.
                        </p>
                    </div>

                    <div class="border-l-4 border-yellow-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/leaderboard</h4>
                        <p class="text-gray-600 text-sm">
                            View the rankings in your group. See who has the most points and who's on a winning streak!
                        </p>
                    </div>

                    <div class="border-l-4 border-gray-500 pl-4">
                        <h4 class="font-semibold text-gray-900">/help</h4>
                        <p class="text-gray-600 text-sm">
                            Shows this help information with available commands and how to use them.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Wager Types -->
            <section class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Wager Types</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Binary (Yes/No)</h3>
                        <p class="text-gray-600 mb-2">
                            Simple yes or no questions. Perfect for predictions like "Will it rain tomorrow?" or "Will Team A win?"
                        </p>
                        <div class="bg-gray-50 rounded p-3 text-sm">
                            <strong>Example:</strong> "Will it snow on Christmas Day?"<br>
                            Options: Yes / No
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Multiple Choice</h3>
                        <p class="text-gray-600 mb-2">
                            Choose from multiple options. Great for "Who will win?" or "What color will they choose?"
                        </p>
                        <div class="bg-gray-50 rounded p-3 text-sm">
                            <strong>Example:</strong> "Who will win the Super Bowl?"<br>
                            Options: Chiefs / 49ers / Eagles / Cowboys
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Numeric</h3>
                        <p class="text-gray-600 mb-2">
                            Predict a specific number within a range. Winner can be closest, exact match, highest, or lowest.
                        </p>
                        <div class="bg-gray-50 rounded p-3 text-sm">
                            <strong>Example:</strong> "What will the temperature be at noon?"<br>
                            Range: 60-85°F, Winner: Closest
                        </div>
                    </div>
                </div>
            </section>

            <!-- Challenges -->
            <section class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Challenges</h2>
                <p class="text-gray-700 mb-4">
                    Challenges are 1-on-1 tasks where points are at stake. Unlike wagers, challenges have a challenger and an acceptor.
                </p>

                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Offer Points (Positive Amount)</h3>
                        <p class="text-gray-600 mb-2">
                            You offer points for someone to complete a task. The challenger puts up the points, and the acceptor earns them upon completion.
                        </p>
                        <div class="bg-green-50 rounded p-3 text-sm border border-green-200">
                            <strong>Example:</strong> "I'll give 100 points to whoever brings me coffee"<br>
                            <span class="text-green-800">→ Someone accepts, completes the task, and earns 100 points from you</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Request Points (Negative Amount)</h3>
                        <p class="text-gray-600 mb-2">
                            You request points to complete a task for someone else. The acceptor puts up the points, and the challenger earns them upon completion.
                        </p>
                        <div class="bg-blue-50 rounded p-3 text-sm border border-blue-200">
                            <strong>Example:</strong> "I'll clean the garage for 150 points"<br>
                            <span class="text-blue-800">→ Someone accepts, you complete the task, and you earn 150 points from them</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How Points Work -->
            <section class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">How Points Work</h2>

                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Starting Balance</h3>
                        <p class="text-gray-600">
                            Each member starts with 1000 points when they join a group. Points are tracked separately per group.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Wagering</h3>
                        <p class="text-gray-600">
                            When you join a wager, you stake points on your prediction. All staked points go into the pot.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Winning</h3>
                        <p class="text-gray-600">
                            Winners split the pot proportionally based on their stake. If you wagered 100 points and the total winning stake was 500 points from a pot of 1000, you'd win 200 points (100/500 * 1000).
                        </p>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-900 mb-2">Example Payout:</h3>
                        <div class="text-green-800 space-y-1 text-sm">
                            <p>• Total pot: 1000 points</p>
                            <p>• Winner A wagered: 100 points</p>
                            <p>• Winner B wagered: 150 points</p>
                            <p>• Total winning stake: 250 points</p>
                            <p class="font-semibold mt-2">Payouts:</p>
                            <p>• Winner A: (100/250) × 1000 = 400 points</p>
                            <p>• Winner B: (150/250) × 1000 = 600 points</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Profile Settings -->
            <section class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Profile Settings</h2>

                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Taunt Line</h3>
                        <p class="text-gray-600">
                            Set a custom victory message that will be displayed when you win wagers. Let your personality shine!
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Birthday</h3>
                        <p class="text-gray-600">
                            Add your birthday and the bot will automatically send birthday wishes in your groups!
                        </p>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Frequently Asked Questions</h2>

                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">Can I change my prediction after joining?</h3>
                        <p class="text-gray-600">No, predictions are final once submitted to ensure fair play.</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900">What happens if the deadline passes?</h3>
                        <p class="text-gray-600">The wager is locked and no more participants can join. The creator can then settle it with the outcome.</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900">Can I run out of points?</h3>
                        <p class="text-gray-600">Yes, manage your points wisely! If you run out, you won't be able to join new wagers until you win some back.</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900">Who can settle wagers?</h3>
                        <p class="text-gray-600">Only the wager creator or group admins can settle wagers to ensure trusted outcomes.</p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900">Are points transferred between groups?</h3>
                        <p class="text-gray-600">No, each group has its own separate point economy and leaderboard.</p>
                    </div>
                </div>
            </section>

            <!-- Support -->
            <div class="mt-8 text-center text-gray-600">
                <p>Still have questions? Contact us on Telegram or visit our documentation.</p>
            </div>
        </div>
    </AppLayout>
</template>
