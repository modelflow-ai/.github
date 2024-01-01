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
 * Inspired by https://github.com/theodo-group/LLPhant/blob/4825d36/src/Embeddings/DocumentSplitter/DocumentSplitter.php.
 */
interface EmbeddingSplitterInterface
{
    /**
     * @return EmbeddingInterface[]
     */
    public function splitEmbedding(EmbeddingInterface $embedding): array;

    /**
     * @param EmbeddingInterface[] $embeddings
     *
     * @return EmbeddingInterface[]
     */
    public function splitEmbeddings(array $embeddings): array;
}
