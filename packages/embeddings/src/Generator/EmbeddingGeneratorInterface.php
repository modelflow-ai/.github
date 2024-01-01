<?php

namespace ModelflowAi\Embeddings\Generator;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

interface EmbeddingGeneratorInterface
{
    /**
     * @param EmbeddingInterface $embedding
     *
     * @return EmbeddingInterface[]
     */
    public function embed(EmbeddingInterface $embedding, ?callable $headerGenerator = null): array;
}
