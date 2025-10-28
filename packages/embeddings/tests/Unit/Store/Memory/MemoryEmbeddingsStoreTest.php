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

namespace ModelflowAi\Embeddings\Tests\Unit\Store\Memory;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\Memory\MemoryEmbeddingsStore;
use PHPUnit\Framework\TestCase;

class MemoryEmbeddingsStoreTest extends TestCase
{
    private MemoryEmbeddingsStore $store;

    protected function setUp(): void
    {
        $this->store = new MemoryEmbeddingsStore();
    }

    public function testAddDocument(): void
    {
        $embedding = new TestEmbedding('content1', [0.1, 0.2, 0.3]);

        $this->store->addDocument($embedding);

        // Test the document was added by performing a search that should return it
        $results = $this->store->similaritySearch([0.1, 0.2, 0.3], 1);

        $this->assertCount(1, $results);
        $this->assertSame($embedding->getContent(), $results[0]->getContent());
        $this->assertSame($embedding->getVector(), $results[0]->getVector());
    }

    public function testAddDocuments(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.2, 0.3]);
        $embedding2 = new TestEmbedding('content2', [0.4, 0.5, 0.6]);

        $embeddings = [$embedding1, $embedding2];

        $this->store->addDocuments($embeddings);

        // Test both documents were added by performing a search that should return both
        $results = $this->store->similaritySearch([0.25, 0.35, 0.45], 2);

        $this->assertCount(2, $results);
        // Contents should match, but order might depend on distance calculation
        $contents = [$results[0]->getContent(), $results[1]->getContent()];
        $this->assertContains($embedding1->getContent(), $contents);
        $this->assertContains($embedding2->getContent(), $contents);
    }

    public function testSimilaritySearch(): void
    {
        // Set up test embeddings with different vectors
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1]);
        $embedding2 = new TestEmbedding('content2', [0.2, 0.2, 0.2]);
        $embedding3 = new TestEmbedding('content3', [0.3, 0.3, 0.3]);
        $embedding4 = new TestEmbedding('content4', [0.8, 0.8, 0.8]);
        $embedding5 = new TestEmbedding('content5', [0.9, 0.9, 0.9]);

        // Add embeddings to store
        $this->store->addDocuments([
            $embedding1,
            $embedding2,
            $embedding3,
            $embedding4,
            $embedding5,
        ]);

        // Search vector similar to embedding3
        $searchVector = [0.3, 0.3, 0.3];
        $results = $this->store->similaritySearch($searchVector, 3);

        // Should return embedding3, embedding2, embedding1 in that order (by similarity)
        $this->assertCount(3, $results);
        $this->assertSame($embedding3->getContent(), $results[0]->getContent());
        $this->assertSame($embedding2->getContent(), $results[1]->getContent());
        $this->assertSame($embedding1->getContent(), $results[2]->getContent());
        // Verify scores are set and decrease in order (higher score = better match)
        $this->assertNotNull($results[0]->getScore());
        $this->assertNotNull($results[1]->getScore());
        $this->assertNotNull($results[2]->getScore());
        $this->assertGreaterThanOrEqual($results[1]->getScore(), $results[0]->getScore());
        $this->assertGreaterThanOrEqual($results[2]->getScore(), $results[1]->getScore());
    }

    public function testSimilaritySearchWithNullVector(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1]);
        $embedding2 = new TestEmbedding('content2', null);
        $embedding3 = new TestEmbedding('content3', [0.3, 0.3, 0.3]);

        // Add embeddings to store
        $this->store->addDocuments([
            $embedding1,
            $embedding2,
            $embedding3,
        ]);

        // Search vector
        $searchVector = [0.2, 0.2, 0.2];
        $results = $this->store->similaritySearch($searchVector, 3);

        // Should skip embedding2 because its vector is null
        $this->assertCount(2, $results);
        // The order should be based on distance from the search vector
        $contents = [$results[0]->getContent(), $results[1]->getContent()];
        $this->assertContains($embedding1->getContent(), $contents);
        $this->assertContains($embedding3->getContent(), $contents);
    }

    public function testSimilaritySearchWithAdditionalArguments(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1], 'article');
        $embedding2 = new TestEmbedding('content2', [0.2, 0.2, 0.2], 'blog');
        $embedding3 = new TestEmbedding('content3', [0.3, 0.3, 0.3], 'article');

        // Add embeddings to store
        $this->store->addDocuments([
            $embedding1,
            $embedding2,
            $embedding3,
        ]);

        // Search vector with category filter
        $searchVector = [0.2, 0.2, 0.2];
        $results = $this->store->similaritySearch($searchVector, 3, ['category' => 'article']);

        // Should only return article embeddings
        $this->assertCount(2, $results);
        $contents = [$results[0]->getContent(), $results[1]->getContent()];
        $this->assertContains($embedding1->getContent(), $contents);
        $this->assertContains($embedding3->getContent(), $contents);
    }

    public function testSimilaritySearchWithNestedAdditionalArguments(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1], 'article', ['nested' => 'value1']);
        $embedding2 = new TestEmbedding('content2', [0.2, 0.2, 0.2], 'blog', ['nested' => 'value2']);
        $embedding3 = new TestEmbedding('content3', [0.3, 0.3, 0.3], 'article', ['nested' => 'value1']);

        // Add embeddings to store
        $this->store->addDocuments([
            $embedding1,
            $embedding2,
            $embedding3,
        ]);

        // Search vector with nested property filter
        $searchVector = [0.2, 0.2, 0.2];
        $results = $this->store->similaritySearch($searchVector, 3, ['metadata[nested]' => 'value1']);

        // Should only return embeddings with the matching nested property
        $this->assertCount(2, $results);
        $contents = [$results[0]->getContent(), $results[1]->getContent()];
        $this->assertContains($embedding1->getContent(), $contents);
        $this->assertContains($embedding3->getContent(), $contents);
    }

    public function testSimilaritySearchWithMultipleAdditionalArguments(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1], 'article', ['nested' => 'value1']);
        $embedding2 = new TestEmbedding('content2', [0.2, 0.2, 0.2], 'blog', ['nested' => 'value2']);
        $embedding3 = new TestEmbedding('content3', [0.3, 0.3, 0.3], 'article', ['nested' => 'value1']);
        $embedding4 = new TestEmbedding('content4', [0.4, 0.4, 0.4], 'article', ['nested' => 'value2']);

        // Add embeddings to store
        $this->store->addDocuments([
            $embedding1,
            $embedding2,
            $embedding3,
            $embedding4,
        ]);

        // Search vector with multiple filters
        $searchVector = [0.2, 0.2, 0.2];
        $results = $this->store->similaritySearch($searchVector, 3, [
            'category' => 'article',
            'metadata[nested]' => 'value1',
        ]);

        // Should only return embeddings matching both criteria
        $this->assertCount(2, $results);
        $contents = [$results[0]->getContent(), $results[1]->getContent()];
        $this->assertContains($embedding1->getContent(), $contents);
        $this->assertContains($embedding3->getContent(), $contents);
    }

    public function testSimilaritySearchWithEmptyStore(): void
    {
        $searchVector = [0.1, 0.2, 0.3];
        $results = $this->store->similaritySearch($searchVector);

        $this->assertEmpty($results);
    }

    public function testSimilaritySearchLimitResults(): void
    {
        // Add 5 embeddings
        $embeddings = [];
        for ($i = 0; $i < 5; ++$i) {
            $embeddings[] = new TestEmbedding(
                "content$i",
                [$i / 10, $i / 10, $i / 10],
            );
        }

        $this->store->addDocuments($embeddings);

        // Limit to 2 results
        $results = $this->store->similaritySearch([0.2, 0.2, 0.2], 2);

        $this->assertCount(2, $results);
    }

    public function testSimilaritySearchWithLargerKThanAvailableResults(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1]);
        $embedding2 = new TestEmbedding('content2', [0.2, 0.2, 0.2]);

        $this->store->addDocuments([$embedding1, $embedding2]);

        // Request more results than available
        $results = $this->store->similaritySearch([0.15, 0.15, 0.15], 10);

        // Should only return the available matching documents
        $this->assertCount(2, $results);
    }

    public function testSimilaritySearchWithArrayFilter(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1], 'article');
        $embedding2 = new TestEmbedding('content2', [0.2, 0.2, 0.2], 'blog');
        $embedding3 = new TestEmbedding('content3', [0.3, 0.3, 0.3], 'news');
        $embedding4 = new TestEmbedding('content4', [0.4, 0.4, 0.4], 'tutorial');

        $this->store->addDocuments([$embedding1, $embedding2, $embedding3, $embedding4]);

        // Search with array filter - should match article, blog, and news but not tutorial
        $searchVector = [0.25, 0.25, 0.25];
        $results = $this->store->similaritySearch($searchVector, 4, ['category' => ['article', 'blog', 'news']]);

        $this->assertCount(3, $results);
        $contents = [$results[0]->getContent(), $results[1]->getContent(), $results[2]->getContent()];
        $this->assertContains($embedding1->getContent(), $contents);
        $this->assertContains($embedding2->getContent(), $contents);
        $this->assertContains($embedding3->getContent(), $contents);
        $this->assertNotContains($embedding4->getContent(), $contents);
    }

    public function testSimilaritySearchWithUnsupportedFilterType(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.1, 0.1]);
        $this->store->addDocument($embedding1);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported filter value type');

        $this->store->similaritySearch([0.1, 0.1, 0.1], 1, ['category' => 123]);
    }
}

class TestEmbedding implements EmbeddingInterface
{
    private ?string $formattedContent = null;
    private readonly string $hash;
    private int $chunkNumber = 0;
    protected ?float $score = null;

    /**
     * @param float[]|null $vector
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        private readonly string $content,
        private ?array $vector = null,
        private readonly ?string $category = null,
        private readonly ?array $metadata = null,
    ) {
        $this->hash = \hash('sha256', $content);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFormattedContent(): string
    {
        return $this->formattedContent ?? $this->content;
    }

    public function setFormattedContent(string $formattedContent): void
    {
        $this->formattedContent = $formattedContent;
    }

    public function getVector(): ?array
    {
        return $this->vector;
    }

    public function setVector(array $vector): void
    {
        $this->vector = $vector;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string[]
     */
    public function getIdentifierParts(): array
    {
        return ['test', $this->content];
    }

    public function getIdentifier(): string
    {
        return \implode('-', $this->getIdentifierParts()) . '-' . $this->chunkNumber;
    }

    public function split(string $content, int $chunkNumber): EmbeddingInterface
    {
        $split = new self($content, $this->vector, $this->category, $this->metadata);
        $split->chunkNumber = $chunkNumber;

        return $split;
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @return array{
     *     content: string,
     *     vector: float[]|null,
     *     category: string|null,
     *     metadata: array<string, mixed>|null,
     *     hash: string,
     *     chunkNumber: int,
     *     formattedContent: string|null,
     * }
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'formattedContent' => $this->formattedContent,
            'vector' => $this->vector,
            'hash' => $this->hash,
            'chunkNumber' => $this->chunkNumber,
            'category' => $this->category,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @param array{
     *     content: string,
     *     vector: float[]|null,
     *     category: string|null,
     *     metadata: array<string, mixed>|null,
     * } $data
     */
    public static function fromArray(array $data): EmbeddingInterface
    {
        return new self(
            $data['content'],
            $data['vector'],
            $data['category'],
            $data['metadata'],
        );
    }
}
