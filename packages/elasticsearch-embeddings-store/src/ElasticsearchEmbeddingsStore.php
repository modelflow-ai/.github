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

namespace ModelflowAi\Embeddings\Store\Elasticsearch;

use Elasticsearch\Client;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;

/**
 * Heavenly inspired by LLPhant
 * https://github.com/theodo-group/LLPhant/blob/main/src/Embeddings/VectorStores/Elasticsearch/ElasticsearchVectorStore.php
 * https://www.elastic.co/search-labs/tutorials/search-tutorial/vector-search/nearest-neighbor-search
 */
class ElasticsearchEmbeddingsStore implements EmbeddingsStoreInterface
{
    /**
     * @var callable
     */
    protected $mapper;
    private bool $vectorDimSet = false;

    public function __construct(
        protected Client $client,
        protected readonly string $indexName,
        callable $mapper = null,
        protected readonly array $metadata = [],
    ) {
        $this->mapper = $mapper ?: fn(EmbeddingInterface $embedding) => [];
        $exists = $client->indices()->exists(['index' => $indexName]);
        if ($exists) {
            return;
        }

        $mapping = [
            'index' => $indexName,
            'body' => [
                'mappings' => [
                    'properties' => array_merge([
                        'content' => [
                            'type' => 'text',
                        ],
                        'formattedContent' => [
                            'type' => 'text',
                        ],
                        'sourceType' => [
                            'type' => 'keyword',
                        ],
                        'sourceName' => [
                            'type' => 'keyword',
                        ],
                        'hash' => [
                            'type' => 'keyword',
                        ],
                        'chunkNumber' => [
                            'type' => 'integer',
                        ],
                        'object' => [
                            'type' => 'binary',
                            'store' => true,
                        ],
                    ], $metadata),
                ],
            ],
        ];
        $client->indices()->create($mapping);
    }

    public function addDocument(EmbeddingInterface $embedding): void
    {
        $this->setVectorDimIfNotSet(count($embedding->getVector()));
        $this->store($embedding);
        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    public function addDocuments(array $embeddings): void
    {
        if ($embeddings === []) {
            return;
        }

        $this->setVectorDimIfNotSet(count($embeddings[0]->getVector()));
        foreach ($embeddings as $embedding) {
            $this->addDocument($embedding);
        }
        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array
    {
        $numCandidates = max(50, $k * 4);
        $searchParams = [
            'index' => $this->indexName,
            'body' => [
                'knn' => [
                    'field' => 'embedding',
                    'query_vector' => $vector,
                    'k' => $k,
                    'num_candidates' => $numCandidates,
                ],
                'sort' => [
                    '_score' => [
                        'order' => 'desc',
                    ],
                ],
            ],
        ];

        $searchParams['body']['knn']['filter'] = [];
        foreach ($additionalArguments as $key => $value) {
            $searchParams['body']['knn']['filter'][] = [
                'term' => [
                    $key => $value,
                ],
            ];
        }

        $rawResponse = $this->client->search($searchParams);

        $embeddings = [];
        foreach ($rawResponse['hits']['hits'] as $hit) {
            $embeddings[] = \unserialize(\base64_decode($hit['_source']['object']));
        }

        return $embeddings;
    }

    private function store(EmbeddingInterface $embedding): void
    {
        if ($embedding->getVector() === null) {
            throw new \Exception('Document embedding must be set before adding a document');
        }

        $this->client->index([
            'index' => $this->indexName,
            'body' => array_merge([
                'embedding' => $embedding->getVector(),
                'content' => $embedding->getContent(),
                'formattedContent' => $embedding->getFormattedContent(),
                'hash' => $embedding->getHash(),
                'chunkNumber' => $embedding->getChunkNumber(),
                'object' => \base64_encode(\serialize($embedding)),
            ], call_user_func($this->mapper, $embedding)),
        ]);

        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    private function setVectorDimIfNotSet(int $vectorDim): void
    {
        if ($this->vectorDimSet) {
            return;
        }
        /** @var array{string: array{mappings: array{embedding: array{mapping: array{embedding: array{dims: int}}}}}} $response */
        $response = $this->client->indices()->getFieldMapping([
            'index' => $this->indexName,
            'fields' => 'embedding',
        ]);
        $mappings = $response[$this->indexName]['mappings'];
        if (
            array_key_exists('embedding', $mappings)
            && $mappings['embedding']['mapping']['embedding']['dims'] === $vectorDim
        ) {
            return;
        }

        $this->client->indices()->putMapping([
            'index' => $this->indexName,
            'body' => [
                'properties' => [
                    'embedding' => [
                        'type' => 'dense_vector',
                        'element_type' => 'float',
                        'dims' => $vectorDim,
                        'index' => true,
                        'similarity' => 'cosine',
                    ],
                ],
            ],
        ]);
        $this->vectorDimSet = true;
    }
}
