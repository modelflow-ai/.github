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

namespace ModelflowAi\Embeddings\Store\Qdrant;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use Qdrant\Exception\InvalidArgumentException;
use Qdrant\Models\Filter\Condition\MatchAny;
use Qdrant\Models\Filter\Condition\MatchString;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant;

class QdrantEmbeddingsStore implements EmbeddingsStoreInterface
{
    /**
     * @param int<1, max> $chunkSize
     */
    public function __construct(
        private readonly Qdrant $client,
        private readonly string $collectionName,
        private readonly int $chunkSize = 600,
    ) {
    }

    protected function createCollection(int $embeddingLength): void
    {
        $createCollection = new CreateCollection();

        $createCollection->addVector(
            new VectorParams(
                $embeddingLength,
                VectorParams::DISTANCE_COSINE,
            ),
        );

        $this->client->collections($this->collectionName)->create($createCollection);
    }

    protected function checkCollection(): bool
    {
        try {
            $this->client->collections($this->collectionName)->info();

            return true;
        } catch (InvalidArgumentException $exception) {
            if (404 === $exception->getCode()) {
                return false;
            }

            throw $exception;
        }
    }

    public function addDocument(EmbeddingInterface $embedding): void
    {
        if (!\is_array($embedding->getVector())) {
            throw new \Exception('Impossible to save a document without its vectors.');
        }

        if (!$this->checkCollection()) {
            $this->createCollection(\count($embedding->getVector()));
        }

        $points = new PointsStruct();
        $this->createPointFromDocument($points, $embedding);
        $this->client->collections($this->collectionName)->points()->upsert($points);
    }

    public function addDocuments(array $embeddings): void
    {
        if ([] === $embeddings) {
            return;
        }

        if (!\is_array($embeddings[0]->getVector())) {
            throw new \Exception('Impossible to save a document without its vectors.');
        }

        if (!$this->checkCollection()) {
            $this->createCollection(\count($embeddings[0]->getVector()));
        }

        $chunks = \array_chunk($embeddings, $this->chunkSize);
        foreach ($chunks as $chunk) {
            $points = new PointsStruct();

            foreach ($chunk as $embedding) {
                $this->createPointFromDocument($points, $embedding);
            }

            $this->client->collections($this->collectionName)->points()->upsert($points);
        }
    }

    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array
    {
        $vectorStruct = new VectorStruct($vector);
        $filter = new Filter();

        foreach ($additionalArguments as $key => $value) {
            if (\is_string($value)) {
                $filter->addMust(new MatchString($key, $value));
            } elseif (\is_array($value)) {
                $filter->addMust(new MatchAny($key, $value));
            } else {
                throw new \Exception('Unsupported filter value type');
            }
        }

        $searchRequest = (new SearchRequest($vectorStruct))
            ->setFilter($filter)
            ->setLimit($k)
            ->setParams([
                'hnsw_ef' => 128,
                'exact' => true,
            ])
            ->setWithPayload(true);

        $response = $this->client->collections($this->collectionName)->points()->search($searchRequest);
        $arrayResponse = $response->__toArray();
        $results = $arrayResponse['result'];

        if ((\is_countable($results) ? \count($results) : 0) === 0) {
            return [];
        }

        $embeddings = [];
        foreach ($results as $point) {
            $embeddings[] = $point['payload']['className']::fromArray($point['payload']);
        }

        return $embeddings;
    }

    private function createPointFromDocument(PointsStruct $points, EmbeddingInterface $embedding): void
    {
        if (!\is_array($embedding->getVector())) {
            throw new \Exception('Impossible to save a document without its vectors.');
        }

        $id = $embedding->getIdentifier();

        $payload = $embedding->toArray();
        $payload['id'] = $id;
        $payload['className'] = $embedding::class;

        $points->addPoint(
            new PointStruct(
                $id,
                new VectorStruct($embedding->getVector()),
                $payload,
            ),
        );
    }
}
