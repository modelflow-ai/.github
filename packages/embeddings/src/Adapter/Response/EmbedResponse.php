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
     * @param float[] $vector
     */
    public function __construct(
        private array $vector,
        private EmbeddingUsage $usage,
    ) {
    }

    /**
     * @return float[]
     */
    public function getVector(): array
    {
        return $this->vector;
    }

    public function getUsage(): EmbeddingUsage
    {
        return $this->usage;
    }
}
