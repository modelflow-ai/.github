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

namespace ModelflowAi\Embeddings\Usage;

final readonly class EmbeddingUsage
{
    /**
     * @param EmbeddingUsage[] $usages
     */
    public static function fromUsages(array $usages): self
    {
        $promptTokens = 0;
        $totalTokens = 0;
        foreach ($usages as $usage) {
            $promptTokens += $usage->getPromptTokens();
            $totalTokens += $usage->getTotalTokens();
        }

        return new self($promptTokens, $totalTokens);
    }

    public static function empty(): self
    {
        return new self(0);
    }

    public function __construct(
        private int $promptTokens,
        private ?int $totalTokens = null,
    ) {
    }

    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokens ?? $this->promptTokens;
    }
}
