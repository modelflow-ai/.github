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

namespace ModelflowAi\Embeddings\Store\Elasticsearch\Tests\Functional;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Response\Elasticsearch;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Model\EmbeddingTrait;
use ModelflowAi\Embeddings\Store\Elasticsearch\ElasticsearchEmbeddingsStore;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ElasticsearchEmbeddingsStoreTest extends TestCase
{
    private EmbeddingAdapterInterface $embeddingAdapter;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->embeddingAdapter = new OllamaEmbeddingAdapter(
            Ollama::factory()->withBaseUrl($_ENV['OLLAMA_URL'])->make(),
            'all-minilm',
        );
        $this->client = ClientBuilder::create()->build();
    }

    protected function embed(EmbeddingInterface $embedding): EmbeddingInterface
    {
        $request = new EmbedRequest([$embedding->getContent()]);
        $response = $this->embeddingAdapter->embed($request);
        $embedding->setVector($response->getVectors()[0]);

        return $embedding;
    }

    /**
     * @param array<string, mixed> $search
     *
     * @return array<array{
     *     _id: string,
     * }>
     */
    protected function find(string $indexName, array $search = []): array
    {
        /** @var Elasticsearch $response */
        $response = $this->client->search([
            'index' => $indexName,
            'body' => $search,
        ]);
        $arrayResponse = $response->asArray();

        return $arrayResponse['hits']['hits'];
    }

    /**
     * @return array<array{
     *     _id: string,
     * }>
     */
    protected function findAll(string $indexName): array
    {
        return $this->find($indexName);
    }

    public function testAddDocument(): void
    {
        $indexName = \sprintf('%s_test', \microtime(true));
        $store = new ElasticsearchEmbeddingsStore($this->client, $indexName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('In the moon\'s vast libraries, books flutter their pages to keep dust from settling on their ancient secrets.', $uuid1);
        $embedding2 = new TestEmbedding('Time travelers held a conference last week to discuss next year\'s past events.', $uuid2);

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocument($embedding1);
        $store->addDocument($embedding2);

        $results = $this->findAll($indexName);

        $this->assertCount(2, $results);

        $uuids = \array_map(fn (array $result) => $result['_id'], $results);
        $this->assertContains($embedding1->getIdentifier(), $uuids);
        $this->assertContains($embedding2->getIdentifier(), $uuids);
    }

    public function testAddDocuments(): void
    {
        $indexName = \sprintf('%s_test', \microtime(true));
        $store = new ElasticsearchEmbeddingsStore($this->client, $indexName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('In the moon\'s vast libraries, books flutter their pages to keep dust from settling on their ancient secrets.', $uuid1);
        $embedding2 = new TestEmbedding('Time travelers held a conference last week to discuss next year\'s past events.', $uuid2);

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocuments([$embedding1, $embedding2]);

        $results = $this->findAll($indexName);

        $this->assertCount(2, $results);

        $uuids = \array_map(fn (array $result) => $result['_id'], $results);
        $this->assertContains($embedding1->getIdentifier(), $uuids);
        $this->assertContains($embedding2->getIdentifier(), $uuids);
    }

    public function testSimilaritySearch(): void
    {
        $indexName = \sprintf('%s_test', \microtime(true));
        $store = new ElasticsearchEmbeddingsStore($this->client, $indexName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('In the moon\'s vast libraries, books flutter their pages to keep dust from settling on their ancient secrets.', $uuid1);
        $embedding2 = new TestEmbedding('Time travelers held a conference last week to discuss next year\'s past events.', $uuid2);

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocuments([$embedding1, $embedding2]);

        $request = new EmbedRequest(['I am searching for ancient secrets']);
        $response = $this->embeddingAdapter->embed($request);
        $result = $store->similaritySearch($response->getVectors()[0], 1);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestEmbedding::class, $result[0]);
        $this->assertSame($uuid1, $result[0]->uuid);

        $result = $store->similaritySearch($response->getVectors()[0], 2);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(TestEmbedding::class, $result[0]);
        $this->assertInstanceOf(TestEmbedding::class, $result[1]);
        $this->assertSame($uuid1, $result[0]->uuid);
        $this->assertSame($uuid2, $result[1]->uuid);
    }
}

class TestEmbedding implements EmbeddingInterface
{
    use EmbeddingTrait;

    public function __construct(
        string $content,
        public string $uuid,
    ) {
        $this->content = $content;
        $this->hash = $this->hash($uuid);
    }

    /**
     * @return string[]
     */
    public function getIdentifierParts(): array
    {
        return [$this->uuid];
    }
}
