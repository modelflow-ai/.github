<?php

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
     * @param array<string, scalar> $additionalArguments
     *
     * @return EmbeddingInterface[]
     */
    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array;
}
