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

final class Usage
{
    public static function empty(): self
    {
        return new self(0, 0, 0);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly int $totalTokens,
        public readonly array $metadata = [],
    ) {
    }

    /**
     * Check if this usage data is based on estimation rather than provider-reported values.
     */
    public function isEstimated(): bool
    {
        return (bool) ($this->metadata['estimated'] ?? false);
    }

    /**
     * Combines this usage with another usage object by adding their token counts.
     * Returns the current instance unchanged if the provided usage is null or invalid.
     *
     * @param self|null $nextUsage The usage to add to this one
     *
     * @return self A new Usage instance with combined token counts
     */
    public function add(?self $nextUsage): self
    {
        if (!$nextUsage instanceof self) {
            return $this;
        }

        return new self(
            $this->inputTokens + $nextUsage->inputTokens,
            $this->outputTokens + $nextUsage->outputTokens,
            $this->totalTokens + $nextUsage->totalTokens,
            \array_merge($this->metadata, $nextUsage->metadata),
        );
    }
}
