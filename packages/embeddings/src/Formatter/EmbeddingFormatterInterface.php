<?php

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
