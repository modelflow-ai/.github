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

final readonly class Usage
{
    public static function empty(): self
    {
        return new self(0, 0, 0);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public int $inputTokens,
        public int $outputTokens,
        public int $totalTokens,
        public array $metadata = [],
    ) {
    }

    public function isEstimated(): bool
    {
        return (bool) ($this->metadata['estimated'] ?? false);
    }

    public function add(?self $nextUsage): self
    {
        if (!$nextUsage instanceof self) {
            return $this;
        }

        $combinedEstimated = ($this->metadata['estimated'] ?? false) || ($nextUsage->metadata['estimated'] ?? false);
        $mergedMetadata = \array_merge($this->metadata, $nextUsage->metadata);
        $mergedMetadata['estimated'] = $combinedEstimated;

        return new self(
            $this->inputTokens + $nextUsage->inputTokens,
            $this->outputTokens + $nextUsage->outputTokens,
            $this->totalTokens + $nextUsage->totalTokens,
            $mergedMetadata,
        );
    }
}
