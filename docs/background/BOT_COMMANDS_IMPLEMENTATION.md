# Bot Commands Implementation

This document describes the implementation of bot commands `/mybets`, `/balance`, and `/help`, along with the unified user dashboard.

## Overview

All bot commands follow a consistent pattern:
1. User sends command (in group or DM)
2. Bot generates a OneTimeToken with type='dashboard_access'
3. Bot creates a ShortUrl pointing to the dashboard with the token
4. Bot sends a DM with preview info + link to dashboard
5. If command was in group, bot acknowledges in the group chat

## Implemented Commands

### `/mybets` (alias: `/mywagers`)

**Location:** `app/Http/Controllers/Api/TelegramWebhookController.php:293-383`

**Behavior:**
- Queries user's active wagers (created or joined)
- Shows top 5 wagers sorted by deadline (soonest first)
- Formats message with wager titles and time remaining
- Generates token with `focus: 'wagers'`
- Sends DM with preview + link to `/me?token=...`

**Message Format:**
```
📊 *Your Active Wagers* (5)

⏰ *Closing Soon:*
1. "Will it snow?" - 2d 5h
2. "Who wins Super Bowl?" - 3d 12h
3. "Bitcoin above $50k?" - 5d 8h
...

👉 View all: https://app.url/l/abc123
```

### `/balance`

**Location:** `app/Http/Controllers/Api/TelegramWebhookController.php:388-515`

**Behavior:**
- Context-aware:
  - **In group chat:** Shows balance for that specific group
  - **In DM:** Shows balances across all groups
- Displays recent transactions (last 3)
- Generates token with `focus: 'transactions'` and `group_id` if applicable
- Sends DM with balance info + link to full transaction history

**Message Format (Group Context):**
```
💰 *Your Balance in Team Alpha*

Current: *850 points*

📊 *Recent Activity:*
📈 +200 pts - Won: "Will it rain?"
📉 -100 pts - Lost: "Who wins?"
📈 +50 pts - Bonus points

👉 View full history: https://app.url/l/xyz789
```

**Message Format (DM Context):**
```
💰 *Your Balances Across Groups*

• Team Alpha: *850 pts*
• Friends Group: *1200 pts*
• Work Crew: *500 pts*

📊 *Recent Activity:*
...

👉 View full history: https://app.url/l/xyz789
```

### `/help`

**Location:** `app/Http/Controllers/Api/TelegramWebhookController.php:167-217`

**Behavior:**
- Sends comprehensive help message as DM
- Lists all available commands
- Shows quick start guide (4 steps)
- Creates short URL to `/help` page for full documentation
- Falls back to sending in chat if DM fails

**Message Format:**
```
📖 *BeatWager Help*

*Available Commands:*

• `/newwager` - Create a new wager in a group
• `/mybets` - View your active wagers
• `/balance` - Check your points balance
• `/leaderboard` - View group rankings
• `/help` - Show this help message

*How it works:*
1️⃣ Create a wager with `/newwager`
2️⃣ Friends join with their predictions
3️⃣ When the event happens, settle the wager
4️⃣ Winners split the pot proportionally!

📚 Full documentation: https://app.url/l/help01
```

## Unified Dashboard (`/me`)

**Controller:** `app/Http/Controllers/DashboardController.php`
**View:** `resources/js/Pages/Dashboard/Me.vue`
**Routes:**
- `GET /me` - Show dashboard
- `POST /me/profile` - Update profile settings

### Authentication

Dashboard uses token-based authentication via `OneTimeToken`:
- Type: `dashboard_access`
- Expiry: 24 hours
- Reusable within expiry window (unlike single-use wager tokens)
- Context includes: `platform`, `platform_user_id`, `focus`, `group_id`

### Dashboard Sections

#### 1. **Overview Tab** (Default)
- Stats cards: Total Balance, Active Wagers, Win Rate, Groups
- Groups list with individual balances
- Top 5 active wagers preview

#### 2. **Wagers Tab**
- **Active Wagers:** All open wagers user created or joined
  - Shows title, group, deadline countdown
  - Indicates if user is creator or participant
  - Shows user's answer and points wagered
  - Link to wager detail page
- **Settled Wagers:** Last 10 completed wagers
  - Shows outcome, win/loss status
  - Displays payout amount for winners
  - Relative time since settlement

#### 3. **Transactions Tab**
- Last 20 transactions
- Shows: type, amount, balance before/after
- Links to related wager if applicable
- Grouped by group if context provided

#### 4. **Profile Tab**
- **Taunt Line:** Custom victory message (max 255 chars)
- **Birthday:** For automated birthday wishes in groups
- Save button with loading state

### Data Provided to Dashboard

**User Data:**
- Basic info: id, name, telegram_username
- Profile: taunt_line, birthday

**Stats:**
- total_balance (sum across all groups)
- active_wagers (count)
- win_rate (percentage)
- total_wagers, won_wagers

**Collections:**
- groups (with balances)
- activeWagers (with user's entry data)
- settledWagers (with outcomes and payouts)
- recentTransactions (with related wager/group)

**Context:**
- focus (overview|wagers|transactions|profile)
- token (for profile updates)

## Help Page (`/help`)

**View:** `resources/js/Pages/Help.vue`
**Route:** `GET /help`

### Content Sections

1. **Getting Started**
   - Overview of BeatWager
   - Quick start guide (5 steps)

2. **Bot Commands**
   - Detailed explanation of each command
   - Color-coded for visual distinction

3. **Wager Types**
   - Binary (Yes/No)
   - Multiple Choice
   - Numeric
   - Examples for each type

4. **How Points Work**
   - Starting balance (1000 per group)
   - Wagering mechanics
   - Payout calculation with example

5. **Profile Settings**
   - Taunt Line explanation
   - Birthday feature

6. **FAQ**
   - Can I change my prediction?
   - What happens after deadline?
   - Can I run out of points?
   - Who can settle wagers?
   - Are points transferred between groups?

## User Profile Fields

**Migration:** `database/migrations/2025_10_15_105902_add_profile_fields_to_users_table.php`

Added fields:
- `taunt_line` (text, nullable) - Custom victory message
- `birthday` (date, nullable) - For birthday automation

## Implementation Notes

### Error Handling

All bot commands handle the case where user hasn't started a DM with the bot:

```php
try {
    $this->bot->sendMessage($userId, $message, 'Markdown');
    // Acknowledge in group if applicable
} catch (\Exception $e) {
    \Log::error('Failed to send DM', ['user_id' => $userId]);
    // Fallback behavior
}
```

### Token Reusability

Dashboard tokens are reusable within their 24-hour expiry window:

```php
if ($token->isUsed()) {
    // Allow reuse of dashboard tokens within expiry window
    // (unlike wager_creation tokens which are single-use)
}
```

### Short URL Expiry

- Dashboard links: 24 hours (matches token expiry)
- Help page link: 1 month (static content)

### Time Formatting

Dashboard includes helper functions for user-friendly time displays:
- `formatTimeRemaining()` - For deadlines (e.g., "2d 5h")
- `formatRelativeTime()` - For past events (e.g., "3h ago")
- `formatDate()` - For absolute dates (e.g., "Jan 15, 2025")

## Testing Checklist

- [ ] `/mybets` command in group → sends DM with wagers
- [ ] `/mybets` in DM → sends wagers directly
- [ ] `/balance` in group → shows group-specific balance
- [ ] `/balance` in DM → shows all group balances
- [ ] `/help` command → sends help DM with link
- [ ] Click dashboard link → loads with correct focus tab
- [ ] Update taunt line → saves successfully
- [ ] Update birthday → saves successfully
- [ ] Token expiry → 403 error after 24 hours
- [ ] Short URL works → redirects to dashboard
- [ ] Help page accessible → full documentation loads

## Files Modified/Created

### Created:
- `app/Http/Controllers/DashboardController.php`
- `resources/js/Pages/Dashboard/Me.vue`
- `resources/js/Pages/Help.vue`
- `database/migrations/2025_10_15_105902_add_profile_fields_to_users_table.php`

### Modified:
- `app/Http/Controllers/Api/TelegramWebhookController.php`
  - Implemented `handleMyWagersCommand()` (lines 293-383)
  - Implemented `handleBalanceCommand()` (lines 388-515)
  - Updated `handleHelpCommand()` (lines 167-217)
- `app/Models/User.php`
  - Added `taunt_line` and `birthday` to fillable
  - Added `birthday` to casts
- `routes/web.php`
  - Added `/me` route (dashboard.me)
  - Added `/me/profile` route (dashboard.profile.update)
  - Added `/help` route

## Next Steps

1. Run migration: `php artisan migrate`
2. Test bot commands in Telegram
3. Verify dashboard loads correctly with token
4. Test profile updates
5. Check help page accessibility
6. Consider adding:
   - Birthday automation job
   - Taunt line display in settlement messages
   - Dashboard analytics/charts
   - Export transaction history
