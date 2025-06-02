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

namespace ModelflowAi\Embeddings\Store\Qdrant\Tests\Functional;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Model\EmbeddingTrait;
use ModelflowAi\Embeddings\Store\Qdrant\QdrantEmbeddingsStore;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use PHPUnit\Framework\TestCase;
use Qdrant\Config;
use Qdrant\Http\GuzzleClient;
use Qdrant\Models\Filter\Condition\MatchString;
use Qdrant\Models\Filter\Filter;
use Qdrant\Qdrant;
use Ramsey\Uuid\Uuid;

class QdrantEmbeddingsStoreTest extends TestCase
{
    private EmbeddingAdapterInterface $embeddingAdapter;
    private Qdrant $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->embeddingAdapter = new OllamaEmbeddingAdapter(
            Ollama::factory()->withBaseUrl($_ENV['OLLAMA_URL'])->make(),
            'all-minilm',
        );
        $this->client = new Qdrant(new GuzzleClient(new Config('http://localhost', 6333)));
    }

    protected function embed(EmbeddingInterface $embedding): EmbeddingInterface
    {
        $vector = $this->embeddingAdapter->embedText($embedding->getContent());
        $embedding->setVector($vector);

        return $embedding;
    }

    /**
     * @return array<array{
     *     payload: array{
     *         uuid: string,
     *     },
     * }>
     */
    protected function find(string $collectionName, Filter $filter): array
    {
        $response = $this->client->collections($collectionName)->points()->scroll($filter);
        $arrayResponse = $response->__toArray();
        $results = $arrayResponse['result'];

        return $results['points'];
    }

    /**
     * @return array<array{
     *     payload: array{
     *         uuid: string,
     *     },
     * }>
     */
    protected function findAll(string $collectionName): array
    {
        $filter = new Filter();
        $filter->addMust(new MatchString('className', TestEmbedding::class));

        return $this->find($collectionName, $filter);
    }

    public function testAddDocument(): void
    {
        $collectionName = \sprintf('%s_test', \microtime(true));
        $store = new QdrantEmbeddingsStore($this->client, $collectionName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('In the moon\'s vast libraries, books flutter their pages to keep dust from settling on their ancient secrets.', $uuid1);
        $embedding2 = new TestEmbedding('Time travelers held a conference last week to discuss next year\'s past events.', $uuid2);

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocument($embedding1);
        $store->addDocument($embedding2);

        $results = $this->findAll($collectionName);

        $this->assertCount(2, $results);

        $uuids = \array_map(fn (array $result) => $result['payload']['uuid'], $results);
        $this->assertContains($uuid1, $uuids);
        $this->assertContains($uuid2, $uuids);
    }

    public function testAddDocuments(): void
    {
        $collectionName = \sprintf('%s_test', \microtime(true));
        $store = new QdrantEmbeddingsStore($this->client, $collectionName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('In the moon\'s vast libraries, books flutter their pages to keep dust from settling on their ancient secrets.', $uuid1);
        $embedding2 = new TestEmbedding('Time travelers held a conference last week to discuss next year\'s past events.', $uuid2);

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocuments([$embedding1, $embedding2]);

        $results = $this->findAll($collectionName);

        $this->assertCount(2, $results);

        $uuids = \array_map(fn (array $result) => $result['payload']['uuid'], $results);
        $this->assertContains($uuid1, $uuids);
        $this->assertContains($uuid2, $uuids);
    }

    public function testSimilaritySearch(): void
    {
        $collectionName = \sprintf('%s_test', \microtime(true));
        $store = new QdrantEmbeddingsStore($this->client, $collectionName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('In the moon\'s vast libraries, books flutter their pages to keep dust from settling on their ancient secrets.', $uuid1);
        $embedding2 = new TestEmbedding('Time travelers held a conference last week to discuss next year\'s past events.', $uuid2);

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocuments([$embedding1, $embedding2]);

        $result = $store->similaritySearch($this->embeddingAdapter->embedText('I am searching for ancient secrets'), 1);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestEmbedding::class, $result[0]);
        $this->assertSame($uuid1, $result[0]->uuid);

        $result = $store->similaritySearch($this->embeddingAdapter->embedText('I am searching for ancient secrets'), 2);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(TestEmbedding::class, $result[0]);
        $this->assertInstanceOf(TestEmbedding::class, $result[1]);
        $this->assertSame($uuid1, $result[0]->uuid);
        $this->assertSame($uuid2, $result[1]->uuid);
    }

    public function testSimilaritySearchWithStringFilter(): void
    {
        $collectionName = \sprintf('%s_test', \microtime(true));
        $store = new QdrantEmbeddingsStore($this->client, $collectionName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('Content about ancient secrets', $uuid1, 'category1');
        $embedding2 = new TestEmbedding('Content about time travel', $uuid2, 'category2');

        $this->embed($embedding1);
        $this->embed($embedding2);

        $store->addDocuments([$embedding1, $embedding2]);

        $result = $store->similaritySearch(
            $this->embeddingAdapter->embedText('searching'),
            2,
            ['category' => 'category1'],
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestEmbedding::class, $result[0]);
        $this->assertSame($uuid1, $result[0]->uuid);
    }

    public function testSimilaritySearchWithArrayFilter(): void
    {
        $collectionName = \sprintf('%s_test', \microtime(true));
        $store = new QdrantEmbeddingsStore($this->client, $collectionName);

        $uuid1 = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $uuid3 = Uuid::uuid4()->toString();

        $embedding1 = new TestEmbedding('Content about ancient secrets', $uuid1, 'category1');
        $embedding2 = new TestEmbedding('Content about time travel', $uuid2, 'category2');
        $embedding3 = new TestEmbedding('Content about magic', $uuid3, 'category3');

        $this->embed($embedding1);
        $this->embed($embedding2);
        $this->embed($embedding3);

        $store->addDocuments([$embedding1, $embedding2, $embedding3]);

        $result = $store->similaritySearch(
            $this->embeddingAdapter->embedText('searching'),
            3,
            ['category' => ['category1', 'category2']],
        );

        $this->assertCount(2, $result);
        $resultUuids = \array_map(fn ($embedding) => $embedding->uuid, $result); // @phpstan-ignore-line
        $this->assertContains($uuid1, $resultUuids);
        $this->assertContains($uuid2, $resultUuids);
        $this->assertNotContains($uuid3, $resultUuids);
    }

    public function testSimilaritySearchWithUnsupportedFilterType(): void
    {
        $collectionName = \sprintf('%s_test', \microtime(true));
        $store = new QdrantEmbeddingsStore($this->client, $collectionName);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported filter value type');

        $store->similaritySearch(
            $this->embeddingAdapter->embedText('searching'),
            2,
            ['category' => new \stdClass()],
        );
    }
}

class TestEmbedding implements EmbeddingInterface
{
    use EmbeddingTrait;

    public function __construct(
        string $content,
        public string $uuid,
        public ?string $category = null,
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
