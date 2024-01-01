<?php

namespace ModelflowAi\Core\Embeddings;

interface EmbeddingAdapterInterface
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;
}
