<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Dispute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for models that can be disputed (Wager, Challenge, GroupEvent).
 *
 * Requires the model to have:
 * - dispute_id (nullable foreign key)
 * - settled_at or verified_at or similar timestamp
 * - group relationship
 * - settler or verifiedBy relationship (the user who settled/verified)
 */
trait Disputable
{
    /**
     * Get all disputes for this item.
     */
    public function disputes(): MorphMany
    {
        return $this->morphMany(Dispute::class, 'disputable');
    }

    /**
     * Get the currently active dispute (if any).
     */
    public function activeDispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }

    /**
     * Alias for activeDispute() - get the currently active dispute.
     */
    public function dispute(): BelongsTo
    {
        return $this->activeDispute();
    }

    /**
     * Check if this item currently has an active dispute.
     */
    public function isDisputed(): bool
    {
        return $this->dispute_id !== null;
    }

    /**
     * Check if this item can be disputed.
     * Must be settled and within the 72-hour dispute window.
     */
    public function canBeDisputed(): bool
    {
        // Must be settled
        if (!$this->isSettled()) {
            return false;
        }

        // Must not already have an active dispute
        if ($this->isDisputed()) {
            return false;
        }

        // Must be within 72 hours of settlement
        $settlementTime = $this->getSettlementTimestamp();
        if ($settlementTime === null) {
            return false;
        }

        return $settlementTime->diffInHours(now()) <= 72;
    }

    /**
     * Get the time remaining to file a dispute.
     */
    public function getDisputeWindowRemaining(): ?string
    {
        if (!$this->isSettled()) {
            return null;
        }

        $settlementTime = $this->getSettlementTimestamp();
        if ($settlementTime === null) {
            return null;
        }

        $deadline = $settlementTime->copy()->addHours(72);
        if ($deadline->isPast()) {
            return null;
        }

        return $deadline->diffForHumans();
    }

    /**
     * Check if this item is settled.
     * Override in model if needed.
     */
    public function isSettled(): bool
    {
        // Check common status values
        if (property_exists($this, 'status') || isset($this->attributes['status'])) {
            return in_array($this->status, ['settled', 'completed', 'verified']);
        }

        // Fall back to checking settlement timestamp
        return $this->getSettlementTimestamp() !== null;
    }

    /**
     * Get the settlement timestamp.
     * Override in model if the field name differs.
     */
    public function getSettlementTimestamp(): ?\Carbon\Carbon
    {
        // Try common field names
        if ($this->settled_at) {
            return $this->settled_at;
        }

        if ($this->verified_at) {
            return $this->verified_at;
        }

        if ($this->completed_at) {
            return $this->completed_at;
        }

        return null;
    }

    /**
     * Get the user who settled/verified this item.
     * Override in model if the relationship name differs.
     */
    public function getSettler(): ?\App\Models\User
    {
        // Try common relationship names
        if (method_exists($this, 'settler') && $this->settler) {
            return $this->settler;
        }

        if (method_exists($this, 'verifiedBy') && $this->verifiedBy) {
            return $this->verifiedBy;
        }

        return null;
    }

    /**
     * Get the outcome value for this item.
     * Override in model if the field name differs.
     */
    public function getOutcomeValue(): ?string
    {
        // Try common field names
        if (isset($this->outcome_value)) {
            return $this->outcome_value;
        }

        if (isset($this->status)) {
            return $this->status;
        }

        return null;
    }

    /**
     * Get available outcome options for voting.
     * Override in model to provide specific options.
     */
    public function getDisputeOutcomeOptions(): array
    {
        // Default implementation - should be overridden
        return [];
    }
}
