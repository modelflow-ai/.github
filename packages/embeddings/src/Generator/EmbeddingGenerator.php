<?php

namespace ModelflowAi\Embeddings\Generator;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatterInterface;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitterInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;

class EmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public function __construct(
        private readonly EmbeddingSplitterInterface $embeddingSplitter,
        private readonly EmbeddingFormatterInterface $embeddingFormatter,
        private readonly EmbeddingAdapterInterface $embeddingAdapter,
    ) {

    }

    public function embed(EmbeddingInterface $embedding, ?callable $headerGenerator = null): array
    {
        $result = [];
        foreach ($this->embeddingSplitter->splitEmbedding($embedding) as $splitEmbedding) {
            $result[] = $newEmbedding = $this->embeddingFormatter->formatEmbedding(
                $splitEmbedding,
                $headerGenerator ? $headerGenerator($embedding) : '',
            );
            $newEmbedding->setVector($this->embeddingAdapter->embedText($newEmbedding->getContent()));
        }

        return $result;
    }
}
