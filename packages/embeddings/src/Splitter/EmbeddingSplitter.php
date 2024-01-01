<?php

namespace ModelflowAi\Embeddings\Splitter;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

class EmbeddingSplitter implements EmbeddingSplitterInterface
{
    public function __construct(
        private int $maxLength = 1000,
        private string $separator = ' ',
    ) {

    }

    public function splitEmbedding(
        EmbeddingInterface $embedding,
    ): array {
        $text = $embedding->getContent();
        if (empty($text)) {
            return [];
        }
        if ($this->maxLength <= 0) {
            return [];
        }

        if ($this->separator === '') {
            return [];
        }

        if (strlen($text) <= $this->maxLength) {
            return [$embedding];
        }

        $chunks = [];
        $words = explode($this->separator, $text);
        $currentChunk = '';

        foreach ($words as $word) {
            if (strlen($currentChunk . $this->separator . $word) <= $this->maxLength || empty($currentChunk)) {
                if (empty($currentChunk)) {
                    $currentChunk = $word;
                } else {
                    $currentChunk .= $this->separator . $word;
                }
            } else {
                $chunks[] = trim($currentChunk);
                $currentChunk = $word;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }
        $splitted = [];
        $chunkNumber = 0;
        foreach ($chunks as $chunk) {
            $newDocument = $embedding->split($chunk, $chunkNumber);
            $chunkNumber++;
            $splitted[] = $newDocument;
        }

        return $splitted;
    }

    public function splitEmbeddings(array $embeddings): array
    {
        $splitted = [];
        foreach ($embeddings as $embedding) {
            $splitted = array_merge(
                $splitted,
                $this->splitEmbedding($embedding),
            );
        }

        return $splitted;
    }
}
