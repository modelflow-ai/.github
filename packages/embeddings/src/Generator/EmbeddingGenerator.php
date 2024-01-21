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

namespace ModelflowAi\Embeddings\Generator;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatterInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitterInterface;

class EmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public function __construct(
        private readonly EmbeddingSplitterInterface $embeddingSplitter,
        private readonly EmbeddingFormatterInterface $embeddingFormatter,
        private readonly EmbeddingAdapterInterface $embeddingAdapter,
    ) {
    }

    public function generateEmbedding(EmbeddingInterface $embedding, ?callable $headerGenerator = null): array
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

    public function generateEmbeddings(array $embeddings, ?callable $headerGenerator = null): array
    {
        $result = [];
        foreach ($embeddings as $embedding) {
            $result = \array_merge($result, $this->generateEmbedding($embedding, $headerGenerator));
        }

        return $result;
    }
}
