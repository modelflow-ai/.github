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
    public function __construct(
        public int $inputTokens,
        public int $outputTokens,
        public int $totalTokens,
    ) {
    }

    public function add(?self $nextUsage): self
    {
        if (!$nextUsage instanceof self) {
            return $this;
        }

        $clone = clone $this;
        $clone->inputTokens += $nextUsage->inputTokens;
        $clone->outputTokens += $nextUsage->outputTokens;
        $clone->totalTokens += $nextUsage->totalTokens;

        return $clone;
    }
}
