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

namespace ModelflowAi\Embeddings\Adapter\Response;

use ModelflowAi\Embeddings\Usage\EmbeddingUsage;

final readonly class EmbedResponse
{
    /**
     * @param float[][] $vectors Array of vectors (always array of arrays, never empty)
     */
    public function __construct(
        private array $vectors,
        private EmbeddingUsage $usage,
    ) {
        if ([] === $vectors) {
            throw new \InvalidArgumentException('EmbedResponse requires at least one vector.');
        }
    }

    /**
     * @return float[][]
     */
    public function getVectors(): array
    {
        return $this->vectors;
    }

    public function count(): int
    {
        return \count($this->vectors);
    }

    public function getUsage(): EmbeddingUsage
    {
        return $this->usage;
    }
}
