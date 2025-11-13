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

    public function registerCallback(UsageCallbackInterface $callback): void
    {
        $this->callbacks[] = $callback;

        if ($this->currentUsage instanceof Usage) {
            $callback->onUsageUpdate($this->currentUsage, $this->isFinalized);
        }
    }

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

    public function getUsage(): ?Usage
    {
        return $this->currentUsage;
    }

    public function isEstimated(): bool
    {
        return $this->isEstimated;
    }

    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }
}
