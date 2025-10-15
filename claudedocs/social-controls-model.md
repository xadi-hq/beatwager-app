# Social Controls Model

## Philosophy

BeatWager uses **social controls** instead of strict admin permissions for wager settlement.

## Key Principles

### 1. Trust-Based Settlement
- **Any group member** can settle wagers (not just admins/creators)
- Settlement actions are tracked in `audit_logs`
- Group transparency creates accountability

### 2. Audit Trail
```php
AuditService::log(
    action: 'wager.settled',
    auditable: $wager,
    metadata: [
        'outcome_value' => $outcomeValue,
        'settlement_note' => $settlementNote,
        'winners_count' => $winners->count(),
        'total_pot' => $wager->total_points_wagered,
    ],
    actor: $settler // The group member who settled
);
```

### 3. Transparency
- All group members can see who settled each wager
- Settlement notes provide reasoning
- Disputes are resolved socially within the group

## Implementation

### Settlement Authorization

**Current Pattern** (already in use):
```php
// Any authenticated group member can settle
public function settleFromShow(Request $request, $wagerId)
{
    $wager = Wager::findOrFail($wagerId);
    $settler = Auth::user(); // Any user, not just admin

    // Settle with settler_id for audit trail
    $this->wagerService->settleWager(
        $wager,
        $validated['outcome_value'],
        $validated['settlement_note'],
        $settler->id // Tracked in audit_logs
    );
}
```

### Database Schema

**Wagers Table:**
```sql
settler_id UUID NULLABLE -- Foreign key to users table
settlement_note TEXT NULLABLE -- Reasoning for settlement
settled_at TIMESTAMP NULLABLE
```

**Audit Logs Table:**
```sql
actor_id UUID -- The user who performed the action
action VARCHAR -- 'wager.settled'
metadata JSON -- Settlement details
created_at TIMESTAMP
```

## Benefits

✅ **Faster Settlement**: No bottleneck waiting for admin
✅ **Group Ownership**: Members feel responsible for fair outcomes
✅ **Flexibility**: Works well for friend groups and communities
✅ **Accountability**: Audit trail prevents abuse
✅ **Social Pressure**: Group norms enforce fairness

## Edge Cases

### What if someone settles unfairly?

**Social Resolution:**
1. Group members can see audit logs
2. Discuss and resolve within the group
3. Can create new wager if needed ("rematch")
4. Extreme cases: Group admin can remove problematic member

**Technical Safeguards:**
- Settlement is final (no editing)
- Audit trail is immutable
- All actions are timestamped
- Settler identity is always recorded

### What about complex settlements?

**Examples in Phase 2A:**

**Short Answer** (e.g., "Who will be topscorer?"):
- Multiple answers may be correct: "Messi", "L. Messi", "mesi"
- Settler selects matching entries via checkboxes
- All selected entries split the pot
- Settlement note explains matching criteria

**Top N Ranking** (e.g., "Top 3 parties"):
- Settler enters actual ranking
- Exact matches win automatically
- No subjective judgment needed

## Phase 2A Considerations

### Settlement UI Design

**Short Answer Settlement:**
```
Settle: "Who will be topscorer?"

All Answers:
☑ L. Messi (3 entries)
☑ Messi (2 entries)
☐ Ronaldo (1 entry)
☑ mesi (1 entry)

Note: "All variants of Messi are correct based on official stats"

[Settle Wager]
```

**Top N Ranking Settlement:**
```
Settle: "Top 3 Dutch parties"

Enter actual results:
1. [Dropdown: PVV]
2. [Dropdown: CDA]
3. [Dropdown: D66]

Note: "Based on official election results"

[Settle Wager]

Winners (exact matches only):
- User A: [PVV, CDA, D66] ✅
- User B: [PVV, VVD, D66] ❌
```

## Comparison to Admin-Only Model

### Admin-Only (Traditional)
❌ Settlement bottleneck
❌ Requires admin hierarchy
❌ Less group ownership
✅ More control

### Social Controls (BeatWager)
✅ Fast settlement
✅ Democratic participation
✅ Group ownership
✅ Audit trail accountability
⚠️ Requires mature group dynamics

## Best Practices

### For Group Members
1. **Settle fairly**: Use objective criteria
2. **Document reasoning**: Add settlement notes
3. **Be transparent**: Discuss unclear situations
4. **Trust the process**: Social pressure works

### For Implementation
1. **Clear audit trail**: Make it easy to see who settled what
2. **Settlement notes**: Encourage (or require) explanations
3. **Immutable logs**: Settlement can't be edited after the fact
4. **Visible history**: All members can review past settlements

## Migration Note

Existing settlement code already supports this model:
- `settler_id` column exists
- `AuditService::log()` tracks actor
- No admin-only checks in settlement flow

Phase 2A maintains this pattern!
