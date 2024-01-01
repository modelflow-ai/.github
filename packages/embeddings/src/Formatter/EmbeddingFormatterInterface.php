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

namespace ModelflowAi\Embeddings\Formatter;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

interface EmbeddingFormatterInterface
{
    public function formatEmbedding(EmbeddingInterface $embedding, string $header = ''): EmbeddingInterface;

    /**
     * @param EmbeddingInterface[] $embeddings
     *
     * @return EmbeddingInterface[]
     */
    public function formatEmbeddings(array $embeddings, string $header = ''): array;
}
