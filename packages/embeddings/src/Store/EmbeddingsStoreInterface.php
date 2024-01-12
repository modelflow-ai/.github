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

namespace ModelflowAi\Embeddings\Store;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

interface EmbeddingsStoreInterface
{
    public function addDocument(EmbeddingInterface $embedding): void;

    /**
     * @param EmbeddingInterface[] $embeddings
     */
    public function addDocuments(array $embeddings): void;

    /**
     * @param float[] $vector
     * @param array<string, scalar> $additionalArguments
     *
     * @return EmbeddingInterface[]
     */
    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array;
}
