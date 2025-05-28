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

namespace ModelflowAi\Embeddings\Splitter;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

/**
 * A no-operation embedding splitter that returns embeddings unchanged.
 * This is useful when you want to disable embedding splitting completely.
 */
final readonly class NoOpEmbeddingSplitter implements EmbeddingSplitterInterface
{
    public function splitEmbedding(EmbeddingInterface $embedding): array
    {
        return [$embedding];
    }

    public function splitEmbeddings(array $embeddings): array
    {
        return $embeddings;
    }
}
