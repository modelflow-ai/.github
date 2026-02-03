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

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use Webmozart\Assert\Assert;

/**
 * Heavenly inspired by LLPhant
 * https://github.com/theodo-group/LLPhant/blob/main/src/Embeddings/VectorStores/Elasticsearch/ElasticsearchVectorStore.php
 * https://www.elastic.co/search-labs/tutorials/search-tutorial/vector-search/nearest-neighbor-search.
 */
class ElasticsearchEmbeddingsStore implements EmbeddingsStoreInterface
{
    private bool $vectorDimSet = false;

    /**
     * @param array<array{type: string}> $metadata
     */
    public function __construct(
        protected Client $client,
        protected readonly string $indexName,
        protected readonly array $metadata = [],
    ) {
        /** @var Elasticsearch $exists */
        $exists = $client->indices()->exists(['index' => $indexName]);
        if (200 === $exists->getStatusCode()) {
            return;
        }

        $mapping = [
            'index' => $indexName,
            'body' => [
                'mappings' => [
                    'properties' => \array_merge([
                        'content' => [
                            'type' => 'text',
                        ],
                        'formattedContent' => [
                            'type' => 'text',
                        ],
                        'hash' => [
                            'type' => 'keyword',
                        ],
                        'chunkNumber' => [
                            'type' => 'integer',
                        ],
                        'className' => [
                            'type' => 'keyword',
                        ],
                    ], $metadata),
                ],
            ],
        ];
        $client->indices()->create($mapping);
    }

    public function addDocument(EmbeddingInterface $embedding): void
    {
        $vector = $embedding->getVector();
        if (null === $vector) {
            throw new \Exception('Document embedding must be set before adding a document');
        }

        $this->setVectorDimIfNotSet(\count($vector));
        $this->store($embedding);
        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    public function addDocuments(array $embeddings): void
    {
        if ([] === $embeddings) {
            return;
        }

        $vector = $embeddings[0]->getVector();
        if (null === $vector) {
            throw new \Exception('Document embedding must be set before adding a document');
        }

        $this->setVectorDimIfNotSet(\count($vector));
        foreach ($embeddings as $embedding) {
            $this->store($embedding);
        }
        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array
    {
        $numCandidates = \max(50, $k * 4);
        $searchParams = [
            'index' => $this->indexName,
            'body' => [
                'knn' => [
                    'field' => 'vector',
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

        /** @var Elasticsearch $response */
        $response = $this->client->search($searchParams);
        $rawResponse = $response->asArray();

        $embeddings = [];
        foreach ($rawResponse['hits']['hits'] as $hit) {
            // Add similarity score to payload before creating embedding
            $payload = $hit['_source'];
            if (isset($hit['_score'])) {
                $payload['score'] = (float) $hit['_score'];
            }

            $embeddings[] = $payload['className']::fromArray($payload);
        }

        return $embeddings;
    }

    private function store(EmbeddingInterface $embedding): void
    {
        if (null === $embedding->getVector()) {
            throw new \Exception('Document embedding must be set before adding a document');
        }

        $body = $embedding->toArray();
        $body['className'] = $embedding::class;

        $this->client->index([
            'index' => $this->indexName,
            'id' => $embedding->getIdentifier(),
            'body' => $body,
        ]);

        $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    private function setVectorDimIfNotSet(int $vectorDim): void
    {
        if ($this->vectorDimSet) {
            return;
        }
        /** @var Elasticsearch $response */
        $response = $this->client->indices()->getFieldMapping([
            'index' => $this->indexName,
            'fields' => 'vector',
        ]);

        /** @var array<string, array{
         *     mappings: array{
         *         vector: array{mapping: array{embedding: array{dims: int}}}|null,
         *     },
         * }> $rawResponse */
        $rawResponse = $response->asArray();

        $mappings = $rawResponse[$this->indexName]['mappings'];
        if ($vectorDim === ($mappings['vector']['mapping']['embedding']['dims'] ?? null)) {
            $this->vectorDimSet = true;

            return;
        }

        $this->client->indices()->putMapping([
            'index' => $this->indexName,
            'body' => [
                'properties' => [
                    'vector' => [
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

    public function removeDocument(string $identifier): void
    {
        Assert::stringNotEmpty($identifier, 'Document identifier cannot be empty');

        try {
            $this->client->delete([
                'index' => $this->indexName,
                'id' => $identifier,
            ]);

            $this->client->indices()->refresh(['index' => $this->indexName]);
        } catch (\Elastic\Elasticsearch\Exception\ClientResponseException $e) {
            if (404 !== $e->getCode()) {
                throw $e;
            }
        }
    }

    /**
     * @param string[] $identifiers
     */
    public function removeDocuments(array $identifiers): void
    {
        if ([] === $identifiers) {
            return;
        }

        foreach ($identifiers as $identifier) {
            Assert::stringNotEmpty($identifier, 'Document identifier cannot be empty');
        }

        $body = [];
        foreach ($identifiers as $identifier) {
            $body[] = ['delete' => ['_index' => $this->indexName, '_id' => $identifier]];
        }

        try {
            /** @var Elasticsearch $bulkResponse */
            $bulkResponse = $this->client->bulk(['body' => $body]);
            $response = $bulkResponse->asArray();

            // Check bulk response for per-item failures
            if (isset($response['errors']) && true === $response['errors']) {
                $items = $response['items'] ?? [];
                if (!\is_array($items)) {
                    $items = [];
                }

                foreach ($items as $item) {
                    if (!\is_array($item)) {
                        continue;
                    }

                    $operation = $item['delete'] ?? $item['index'] ?? $item['create'] ?? $item['update'] ?? null;
                    if (!\is_array($operation)) {
                        continue;
                    }

                    // Allow 404 (document not found) - idempotent deletion
                    $hasError = isset($operation['error']);
                    $status = $operation['status'] ?? 0;

                    if ($hasError && 404 !== $status) {
                        $errorReason = 'Unknown error';
                        if (\is_array($operation['error']) && isset($operation['error']['reason'])) {
                            $reason = $operation['error']['reason'];
                            $errorReason = \is_string($reason) ? $reason : 'Unknown error';
                        }

                        throw new \RuntimeException('Bulk delete operation failed: ' . $errorReason);
                    }
                }
            }

            $this->client->indices()->refresh(['index' => $this->indexName]);
        } catch (\Elastic\Elasticsearch\Exception\ClientResponseException $e) {
            if (!\str_contains($e->getMessage(), 'not_found') && 404 !== $e->getCode()) {
                throw $e;
            }
        }
    }
}
