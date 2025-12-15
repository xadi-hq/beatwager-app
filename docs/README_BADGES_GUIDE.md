# BeatWager Badge Generation Guide

## Badge Reference Tables

### Events

| Achievement | Icon | Background | Border | Sparkles | Number |
|-------------|------|------------|--------|----------|--------|
| First event created | Calendar + star/sparkle | Blue #2196F3 | Gray | Yes | No |
| First event attended | Calendar + checkmark | Green #4CAF50 | Gray | Yes | No |
| 5 event streak | Calendar + flame | Orange #F57C00 | Bronze | No | "5" |
| 10 event streak | Calendar + flame | Deep orange #E64A19 | Silver | No | "10" |
| 20 event streak | Calendar + flame | Red-orange #D84315 | Gold | No | "20" |
| First no-show | Ghost (friendly) | Gray #757575 | Gray | No | No |
| 5th no-show (shame) | Ghost with X eyes | Dark red #B71C1C | Bronze | No | "5" |

### Challenges

| Achievement | Icon | Background | Border | Sparkles | Number |
|-------------|------|------------|--------|----------|--------|
| First request challenge | Open palm (receiving) + coin | Teal #00897B | Gray | Yes | No |
| First given challenge | Hand reaching down + coin | Purple #7B1FA2 | Gray | Yes | No |
| Group super challenge | Trophy with small figures | Gold #FFC107 | Gray | Yes | No |
| First elimination tap-out | Fallen domino or "out" hand | Orange #FF9800 | Gray | Yes | No |
| Elimination winner | Crown | Gold #FFC107 | Gray | Yes | No |

### Wagers

| Achievement | Icon | Background | Border | Sparkles | Number |
|-------------|------|------------|--------|----------|--------|
| First wager created | Handshake | Blue #1565C0 | Gray | Yes | No |
| First wager won | Thumbs up | Green #2E7D32 | Gray | Yes | No |
| 3rd wager lost | Thumbs down | Muted orange #E65100 | Gray | No | "3" |
| 5 wagers won | Thumbs up | Green #2E7D32 | Bronze | No | "5" |
| 10 wagers won | Thumbs up | Green #388E3C | Silver | No | "10" |
| Most wagers settled | Gavel or balanced scale | Navy #1565C0 | Gold | No | No |

### Disputes

| Achievement | Icon | Background | Border | Sparkles | Number |
|-------------|------|------------|--------|----------|--------|
| Fraudster | Pinocchio nose or mask | Dark red #C62828 | Gray | No | No |
| Judge | Gavel or judge wig | Royal purple #5E35B1 | Gray | Yes | No |
| 5 disputes judged | Gavel or judge wig | Royal purple #5E35B1 | Bronze | No | "5" |
| 10 disputes judged | Gavel or judge wig | Royal purple #5E35B1 | Silver | No | "10" |

---

## Tier System

| Tier | When Earned | Border Description | Hex |
|------|-------------|-------------------|-----|
| Standard | 1st / base | Soft gray with subtle depth | #9E9E9E |
| Bronze | 5 count | Warm bronze with metallic sheen | #CD7F32 |
| Silver | 10 count | Polished silver with metallic sheen | #C0C0C0 |
| Gold | 20 count | Rich gold with metallic sheen | #FFD700 |
| Platinum | 50+ count | Platinum/white gold with extra shine | #E5E4E2 |

---

## Color Palette by Category

| Category | Background Color | Hex | Use For |
|----------|-----------------|-----|---------|
| Events (positive) | Vibrant green | #4CAF50 | First attended, participation |
| Events (streak) | Vibrant orange | #F57C00 | Streaks, consistency |
| Events (negative) | Muted red | #B71C1C | No-shows, shame badges |
| Challenges | Rich teal | #00897B | Challenge completion |
| Challenges (special) | Purple | #7B1FA2 | Group/super challenges |
| Wagers (win) | Victory green | #2E7D32 | Wins, successful bets |
| Wagers (loss) | Muted orange | #E65100 | Losses |
| Wagers (neutral) | Navy blue | #1565C0 | Created, settled, general |
| Disputes (negative) | Dark red | #C62828 | Fraudster |
| Disputes (positive) | Royal purple | #5E35B1 | Judge contributions |

---

## Reusable Prompts

### Template: First Achievement (with sparkles, no number)

```
Create a circular badge icon in the style of classic Foursquare/app achievement badges.

**CRITICAL STYLE REQUIREMENTS (match exactly):**
- Perfectly circular badge with a soft gray outer ring (~8% of badge diameter)
- The border should have subtle 3D depth - slightly raised/beveled appearance
- Inner circle filled with a single vibrant, saturated color
- White icon centered in the badge, simplified and chunky
- Icon should have very subtle soft depth (gentle highlight, no harsh outlines or strokes)
- Gold/yellow 4-point star sparkles (2-3) around the icon
- Overall feel: friendly, polished, app-store quality
- No text whatsoever

**AVOID:**
- Dark outlines/strokes around the icon
- Harsh shadows or gradients
- Overly detailed or complex icons
- Flat/matte appearance (subtle depth is good)
- Corporate or sterile feeling

**Colors for this badge:**
- Border: Soft gray with subtle depth
- Background: [BACKGROUND COLOR]
- Icon: White with very subtle highlight
- Sparkles: Gold/yellow 4-point stars

**This badge represents:** [ACHIEVEMENT DESCRIPTION]

**Icon:** [ICON DESCRIPTION]

Output: Single badge, perfectly circular, transparent background outside the badge, PNG format, 512x512px
```

### Template: Streak/Count Achievement (with number bubble)

```
Create a circular badge icon in the style of classic Foursquare/app achievement badges.

**CRITICAL STYLE REQUIREMENTS (match exactly):**
- Perfectly circular badge with a [BORDER: soft gray / warm bronze / polished silver / rich gold] metallic outer ring (~8% of badge diameter)
- The border should have subtle 3D depth - slightly raised/beveled appearance with metallic sheen
- Inner circle filled with a single vibrant, saturated color
- White icon centered in the badge, simplified and chunky
- Icon should have very subtle soft depth (gentle highlight, no harsh outlines or strokes)
- Small circular number bubble attached to the bottom-right of the main badge, overlapping slightly
- Overall feel: friendly, polished, app-store quality
- No text on the main badge itself

**AVOID:**
- Dark outlines/strokes around the icon
- Harsh shadows or gradients
- Overly detailed or complex icons
- Flat/matte appearance (subtle depth is good)
- Corporate or sterile feeling

**Colors for this badge:**
- Border: [BORDER COLOR] with metallic sheen
- Background: [BACKGROUND COLOR]
- Icon: White with very subtle highlight
- Number bubble: Darker shade of background, matching border color on bubble edge, white bold "[NUMBER]"

**This badge represents:** [ACHIEVEMENT DESCRIPTION]

**Icon:** [ICON DESCRIPTION]

**Number bubble:** Small circle in bottom-right corner containing "[NUMBER]"

Output: Single badge, perfectly circular, PNG format, 512x512px, the area outside the circular badge must be fully transparent (alpha = 0), not white or off-white. Export as PNG-24 with transparency.
```

### Template: Shame/Negative Badge

```
Create a circular badge icon in the style of classic Foursquare/app achievement badges.

**CRITICAL STYLE REQUIREMENTS (match exactly):**
- Perfectly circular badge with a soft gray outer ring (~8% of badge diameter)
- The border should have subtle 3D depth - slightly raised/beveled appearance
- Inner circle filled with a muted, darker color
- White icon centered in the badge, simplified and chunky
- Icon should have very subtle soft depth (gentle highlight, no harsh outlines or strokes)
- Overall feel: still friendly and playful despite being a "shame" badge - not scary or aggressive
- No text whatsoever

**AVOID:**
- Dark outlines/strokes around the icon
- Harsh shadows or gradients
- Overly detailed or complex icons
- Flat/matte appearance (subtle depth is good)
- Making it look too serious or punishing

**Colors for this badge:**
- Border: Soft gray with subtle depth
- Background: [MUTED/DARK COLOR]
- Icon: White with very subtle highlight

**This badge represents:** [ACHIEVEMENT DESCRIPTION]

**Icon:** [ICON DESCRIPTION - keep playful, not aggressive]

Output: Single badge, perfectly circular, PNG format, 512x512px, the area outside the circular badge must be fully transparent (alpha = 0), not white or off-white. Export as PNG-24 with transparency.
```

---

## Quick Checklist Before Generating

- [ ] Copied correct template (first / streak / shame)
- [ ] Selected correct border tier (gray/bronze/silver/gold/platinum)
- [ ] Chose appropriate background color for category
- [ ] Decided: sparkles (first achievements) or number bubble (counts/streaks)
- [ ] Wrote clear, simple icon description (1-2 elements max)
- [ ] Kept icon description chunky and simplified
