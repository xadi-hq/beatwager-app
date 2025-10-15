# Telegram Markdown Formatting Fix

## Problem
LLM-generated messages contained Markdown syntax (`**bold**`) that wasn't being parsed correctly in Telegram. Messages appeared with literal `**` characters and the Markdown formatting wasn't rendered.

Example:
```
🔥 Big news, guys! Xander just jumped into the **Feyenoord - Panathinaikos** wager with a bold **100 points**! Who's brave enough to follow? 💪
```

The entire message appeared bold, but the `**Feyenoord - Panathinaikos**` text didn't parse correctly.

## Root Cause
The issue was in [TelegramMessenger.php:40](../app/Services/Messengers/TelegramMessenger.php#L40), where messages were sent with `'HTML'` parse mode, but the LLM was generating Markdown syntax.

Additionally, the old `formatMessage()` method wrapped the first line in `<b></b>` tags without converting Markdown syntax to HTML first.

## Solution
Updated the `formatMessage()` method in [TelegramMessenger.php](../app/Services/Messengers/TelegramMessenger.php) to convert Markdown syntax to HTML before sending:

### Conversions Applied
1. **Bold**: `**text**` → `<b>text</b>`
2. **Italic**: `*text*` or `_text_` → `<i>text</i>`
3. **Code**: `` `text` `` → `<code>text</code>`
4. **Key-value pairs**: `Label:` → `<b>Label:</b>` (at start of line)

### Test Coverage
Created comprehensive test suite in [TelegramMessengerFormattingTest.php](../tests/Unit/Messengers/TelegramMessengerFormattingTest.php) with 8 test cases:

- ✓ Converts markdown bold to HTML
- ✓ Converts markdown italic to HTML
- ✓ Converts markdown code to HTML
- ✓ Bolds key-value pairs
- ✓ Handles multiline markdown
- ✓ Doesn't double-escape already formatted HTML
- ✓ Handles empty messages
- ✓ Preserves emojis and special characters

All tests pass ✅

## Files Changed
1. [app/Services/Messengers/TelegramMessenger.php](../app/Services/Messengers/TelegramMessenger.php) - Updated `formatMessage()` method
2. [tests/Unit/Messengers/TelegramMessengerFormattingTest.php](../tests/Unit/Messengers/TelegramMessengerFormattingTest.php) - New test file

## Impact
- LLM-generated messages with Markdown formatting now render correctly in Telegram
- Messages are properly styled with bold, italic, and code formatting
- No breaking changes to existing functionality
- Backward compatible with already-formatted HTML messages
