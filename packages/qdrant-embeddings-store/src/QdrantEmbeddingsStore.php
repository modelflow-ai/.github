<?php

namespace ModelflowAi\Embeddings\Store\Qdrant;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use Qdrant\Config;
use Qdrant\Http\GuzzleClient;
use Qdrant\Qdrant;

class QdrantEmbeddingsStore implements EmbeddingsStoreInterface
{
    public Qdrant $client;

    public function __construct(
        Config $config,
        private string $collectionName,
    ) {
        $this->client = new Qdrant(new GuzzleClient($config));
    }

    public function addDocument(EmbeddingInterface $embedding): void
    {
        // TODO: Implement addDocument() method.
    }

    public function addDocuments(array $embeddings): void
    {
        // TODO: Implement addDocuments() method.
    }

    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array
    {
        // TODO: Implement similaritySearch() method.
    }
}
