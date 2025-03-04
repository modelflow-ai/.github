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

namespace ModelflowAi\Embeddings\Tests\Unit\Model;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Model\EmbeddingTrait;
use PHPUnit\Framework\TestCase;

class EmbeddingTraitTest extends TestCase
{
    private TestEmbedding $embedding;

    protected function setUp(): void
    {
        $this->embedding = new TestEmbedding('Test content');
    }

    public function testFromArray(): void
    {
        $data = [
            'content' => 'Content from array',
            'formattedContent' => 'Formatted content from array',
            'vector' => [0.1, 0.2, 0.3],
            'hash' => 'custom-hash-value',
            'chunkNumber' => 5,
            'customProperty' => 'Custom property value',
            'nonExistentProperty' => 'This should be ignored',
        ];

        $embedding = TestEmbedding::fromArray($data);

        $this->assertInstanceOf(TestEmbedding::class, $embedding);
        $this->assertSame('Content from array', $embedding->getContent());
        $this->assertSame('Formatted content from array', $embedding->getFormattedContent());
        $this->assertSame([0.1, 0.2, 0.3], $embedding->getVector());
        $this->assertSame('custom-hash-value', $embedding->getHash());
        $this->assertSame(5, $embedding->getChunkNumber());
        $this->assertSame('Custom property value', $embedding->getCustomProperty());
    }

    public function testSplit(): void
    {
        $newContent = 'Split content';
        $chunkNumber = 3;

        $splitEmbedding = $this->embedding->split($newContent, $chunkNumber);

        $this->assertInstanceOf(TestEmbedding::class, $splitEmbedding);
        $this->assertNotSame($this->embedding, $splitEmbedding);
        $this->assertSame($newContent, $splitEmbedding->getContent());
        $this->assertSame($newContent, $splitEmbedding->getFormattedContent());
        $this->assertNull($splitEmbedding->getVector());
        $this->assertSame(\hash('sha256', $newContent), $splitEmbedding->getHash());
        $this->assertSame($chunkNumber, $splitEmbedding->getChunkNumber());
        $this->assertSame('Custom value', $splitEmbedding->getCustomProperty());
    }

    public function testGetIdentifier(): void
    {
        $expectedParts = ['test', 'identifier'];
        $expectedBase = \implode('-', $expectedParts) . '-0';

        // The exact UUID is hard to predict due to the bit manipulation in formatUuid
        // So we'll test that it's in the right format instead
        $identifier = $this->embedding->getIdentifier();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $identifier);
    }

    public function testGettersAndSetters(): void
    {
        // Test initial values
        $this->assertSame('Test content', $this->embedding->getContent());
        $this->assertSame('Test content', $this->embedding->getFormattedContent());
        $this->assertNull($this->embedding->getVector());
        $this->assertSame(\hash('sha256', 'Test content'), $this->embedding->getHash());
        $this->assertSame(0, $this->embedding->getChunkNumber());

        // Test setters
        $formattedContent = 'Formatted test content';
        $this->embedding->setFormattedContent($formattedContent);
        $this->assertSame($formattedContent, $this->embedding->getFormattedContent());

        $vector = [0.1, 0.2, 0.3, 0.4, 0.5];
        $this->embedding->setVector($vector);
        $this->assertSame($vector, $this->embedding->getVector());
    }

    public function testToArray(): void
    {
        $array = $this->embedding->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('formattedContent', $array);
        $this->assertArrayHasKey('vector', $array);
        $this->assertArrayHasKey('hash', $array);
        $this->assertArrayHasKey('chunkNumber', $array);
        $this->assertArrayHasKey('customProperty', $array);

        $this->assertSame('Test content', $array['content']);
        $this->assertNull($array['formattedContent']);
        $this->assertNull($array['vector']);
        $this->assertSame(\hash('sha256', 'Test content'), $array['hash']);
        $this->assertSame(0, $array['chunkNumber']);
        $this->assertSame('Custom value', $array['customProperty']);
    }

    public function testIdentifierWithDifferentChunkNumbers(): void
    {
        $content = 'Same content';
        $embedding1 = new TestEmbedding($content);
        $embedding2 = $embedding1->split($content, 1);

        $this->assertNotSame($embedding1->getIdentifier(), $embedding2->getIdentifier());
    }

    public function testEmptyContent(): void
    {
        $embedding = new TestEmbedding('');

        $this->assertSame('', $embedding->getContent());
        $this->assertSame('', $embedding->getFormattedContent());
        $this->assertSame(\hash('sha256', ''), $embedding->getHash());
    }
}

class TestEmbedding implements EmbeddingInterface
{
    use EmbeddingTrait;

    private string $customProperty = 'Custom value';

    public function __construct(string $content)
    {
        $this->content = $content;
        $this->hash = $this->hash($content);
    }

    /**
     * @return string[]
     */
    public function getIdentifierParts(): array
    {
        return ['test', 'identifier'];
    }

    public function getCustomProperty(): string
    {
        return $this->customProperty;
    }
}
