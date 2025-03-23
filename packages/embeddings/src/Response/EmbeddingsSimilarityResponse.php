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

namespace ModelflowAi\Embeddings\Response;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;

final readonly class EmbeddingsSimilarityResponse
{
    /**
     * @param EmbeddingInterface[] $embeddings
     */
    public function __construct(
        private array $embeddings,
        private EmbeddingUsage $usage,
    ) {
    }

    /**
     * @return EmbeddingInterface[]
     */
    public function getEmbeddings(): array
    {
        return $this->embeddings;
    }

    public function getUsage(): EmbeddingUsage
    {
        return $this->usage;
    }
}
