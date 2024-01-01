<?php

namespace ModelflowAi\Embeddings\Formatter;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

class EmbeddingFormatter implements EmbeddingFormatterInterface
{
    public function formatEmbedding(EmbeddingInterface $embedding, string $header = ''): EmbeddingInterface
    {
        $embedding->setFormattedContent($header . $embedding->getContent());

        return $embedding;
    }

    public function formatEmbeddings(array $embeddings, string $header = ''): array
    {
        $formattedDocuments = [];
        foreach ($embeddings as $document) {
            $formattedDocuments[] = $this->formatEmbedding($document, $header);
        }

        return $formattedDocuments;
    }
}
