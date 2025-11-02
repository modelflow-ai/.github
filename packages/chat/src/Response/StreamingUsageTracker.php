<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ModelflowAi\Chat\Response;

/**
 * Internal tracker for managing streaming usage updates and callbacks.
 *
 * This class is responsible for:
 * - Accumulating usage data as it arrives
 * - Notifying registered callbacks of updates
 * - Providing the final accumulated usage
 */
final class StreamingUsageTracker
{
    private ?Usage $currentUsage = null;
    private bool $isFinalized = false;

    /** @var list<UsageCallbackInterface> */
    private array $callbacks = [];

    public function __construct(
        private readonly bool $isEstimated = false,
    ) {
    }

    /**
     * Register a callback to receive usage updates.
     */
    public function registerCallback(UsageCallbackInterface $callback): void
    {
        $this->callbacks[] = $callback;

        if ($this->currentUsage instanceof Usage) {
            $callback->onUsageUpdate($this->currentUsage, $this->isFinalized);
        }
    }

    /**
     * Update the current usage and notify callbacks.
     *
     * @param Usage $usage The new usage data (will be accumulated with existing)
     * @param bool $isFinal Whether this is the final update
     */
    public function updateUsage(Usage $usage, bool $isFinal = false): void
    {
        if ($this->isFinalized) {
            return;
        }

        $this->currentUsage = $this->currentUsage instanceof Usage
            ? $this->currentUsage->add($usage)
            : $usage;

        if ($isFinal) {
            $this->isFinalized = true;
        }

        // Notify all registered callbacks
        foreach ($this->callbacks as $callback) {
            $callback->onUsageUpdate($this->currentUsage, $isFinal);
        }
    }

    /**
     * Get the current accumulated usage.
     *
     * @return Usage|null The current usage, or null if no usage data has been received
     */
    public function getUsage(): ?Usage
    {
        return $this->currentUsage;
    }

    /**
     * Check if usage tracking is based on estimation.
     */
    public function isEstimated(): bool
    {
        return $this->isEstimated;
    }

    /**
     * Check if usage has been finalized.
     */
    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }
}
