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

namespace ModelflowAi\Embeddings\Tests\Store\Filesystem;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\Filesystem\FilesystemEmbeddingsStore;
use PHPUnit\Framework\TestCase;

class FilesystemEmbeddingsStoreTest extends TestCase
{
    private string $testFilePath;
    private FilesystemEmbeddingsStore $store;

    protected function setUp(): void
    {
        $this->testFilePath = \sys_get_temp_dir() . '/modelflow_embeddings_test_' . \uniqid() . '.store';
        $this->store = new FilesystemEmbeddingsStore($this->testFilePath);
    }

    protected function tearDown(): void
    {
        if (\file_exists($this->testFilePath)) {
            \unlink($this->testFilePath);
        }
    }

    public function testAddDocument(): void
    {
        $embedding = new TestEmbedding('content1', [0.1, 0.2, 0.3]);

        $this->store->addDocument($embedding);

        $this->assertFileExists($this->testFilePath);

        /** @var TestEmbedding[] $storedEmbeddings */
        $storedEmbeddings = \unserialize(\file_get_contents($this->testFilePath)); // @phpstan-ignore-line
        $this->assertCount(1, $storedEmbeddings);
        $this->assertSame($embedding->getContent(), $storedEmbeddings[0]->getContent());
        $this->assertSame($embedding->getVector(), $storedEmbeddings[0]->getVector());
    }

    public function testAddDocumentToExistingFile(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.2, 0.3]);
        $embedding2 = new TestEmbedding('content2', [0.4, 0.5, 0.6]);

        // Add first document
        $this->store->addDocument($embedding1);

        // Add second document
        $this->store->addDocument($embedding2);

        /** @var TestEmbedding[] $storedEmbeddings */
        $storedEmbeddings = \unserialize(\file_get_contents($this->testFilePath)); // @phpstan-ignore-line
        $this->assertCount(2, $storedEmbeddings);
        $this->assertSame($embedding1->getContent(), $storedEmbeddings[0]->getContent());
        $this->assertSame($embedding1->getVector(), $storedEmbeddings[0]->getVector());
        $this->assertSame($embedding2->getContent(), $storedEmbeddings[1]->getContent());
        $this->assertSame($embedding2->getVector(), $storedEmbeddings[1]->getVector());
    }

    public function testAddDocuments(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.2, 0.3]);
        $embedding2 = new TestEmbedding('content2', [0.4, 0.5, 0.6]);

        $embeddings = [$embedding1, $embedding2];

        $this->store->addDocuments($embeddings);

        /** @var TestEmbedding[] $storedEmbeddings */
        $storedEmbeddings = \unserialize(\file_get_contents($this->testFilePath)); // @phpstan-ignore-line
        $this->assertCount(2, $storedEmbeddings);
        $this->assertSame($embedding1->getContent(), $storedEmbeddings[0]->getContent());
        $this->assertSame($embedding1->getVector(), $storedEmbeddings[0]->getVector());
        $this->assertSame($embedding2->getContent(), $storedEmbeddings[1]->getContent());
        $this->assertSame($embedding2->getVector(), $storedEmbeddings[1]->getVector());
    }

    public function testAddDocumentsToExistingFile(): void
    {
        $embedding1 = new TestEmbedding('content1', [0.1, 0.2, 0.3]);
        $embedding2 = new TestEmbedding('content2', [0.4, 0.5, 0.6]);
        $embedding3 = new TestEmbedding('content3', [0.7, 0.8, 0.9]);

        // Add first document
        $this->store->addDocument($embedding1);

        // Add more documents
        $this->store->addDocuments([$embedding2, $embedding3]);

        /** @var TestEmbedding[] $storedEmbeddings */
        $storedEmbeddings = \unserialize(\file_get_contents($this->testFilePath)); // @phpstan-ignore-line
        $this->assertCount(3, $storedEmbeddings);
        $this->assertSame($embedding1->getContent(), $storedEmbeddings[0]->getContent());
        $this->assertSame($embedding1->getVector(), $storedEmbeddings[0]->getVector());
        $this->assertSame($embedding2->getContent(), $storedEmbeddings[1]->getContent());
        $this->assertSame($embedding2->getVector(), $storedEmbeddings[1]->getVector());
        $this->assertSame($embedding3->getContent(), $storedEmbeddings[2]->getContent());
        $this->assertSame($embedding3->getVector(), $storedEmbeddings[2]->getVector());
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
        $this->assertSame($embedding3->getContent(), $results[0]->getContent());
        $this->assertSame($embedding1->getContent(), $results[1]->getContent());
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
        $this->assertSame($embedding3->getContent(), $results[0]->getContent());
        $this->assertSame($embedding1->getContent(), $results[1]->getContent());
    }

    public function testSimilaritySearchWithEmptyStore(): void
    {
        $searchVector = [0.1, 0.2, 0.3];
        $results = $this->store->similaritySearch($searchVector);

        $this->assertEmpty($results);
    }

    public function testSimilaritySearchWithNonExistentStore(): void
    {
        // Create a new store instance with a non-existent file
        $nonExistentPath = \sys_get_temp_dir() . '/non_existent_file.store';
        $store = new FilesystemEmbeddingsStore($nonExistentPath);

        $searchVector = [0.1, 0.2, 0.3];
        $results = $store->similaritySearch($searchVector);

        $this->assertEmpty($results);
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
    private string $hash;
    private int $chunkNumber = 0;

    /**
     * @param float[]|null $vector
     */
    public function __construct(
        private readonly string $content,
        private ?array $vector = null,
        private readonly ?string $category = null,
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

    /**
     * @return float[]|null
     */
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
        $split = new self($content, $this->vector, $this->category);
        $split->chunkNumber = $chunkNumber;

        return $split;
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return array{
     *     content: string,
     *     formattedContent: string|null,
     *     vector: float[]|null,
     *     hash: string,
     *     chunkNumber: int,
     *     category: string|null,
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
        ];
    }

    /**
     * @param array{
     *     content: string,
     *     formattedContent: string|null,
     *     vector: float[]|null,
     *     hash: string,
     *     chunkNumber: int,
     *     category: string|null,
     * } $data
     */
    public static function fromArray(array $data): EmbeddingInterface
    {
        $embedding = new self($data['content'], $data['vector'], $data['category']);
        $embedding->formattedContent = $data['formattedContent'];
        $embedding->hash = $data['hash'];
        $embedding->chunkNumber = $data['chunkNumber'];

        return $embedding;
    }
}
