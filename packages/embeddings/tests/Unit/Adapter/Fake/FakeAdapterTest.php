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

namespace ModelflowAi\Embeddings\Tests\Unit\Adapter\Fake;

use ModelflowAi\Embeddings\Adapter\Fake\FakeAdapter;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use PHPUnit\Framework\TestCase;

class FakeAdapterTest extends TestCase
{
    public function testEmbedReturnsCorrectEmbedding(): void
    {
        $text = 'Hello world';
        $embedding = [0.1, 0.2, 0.3, 0.4, 0.5];

        $embeddings = [
            $text => $embedding,
        ];

        $adapter = new FakeAdapter($embeddings);

        $result = $adapter->embed(new EmbedRequest([$text]));

        $this->assertSame([$embedding], $result->getVectors());
    }

    public function testEmbedWithMultipleEmbeddings(): void
    {
        $text1 = 'Hello world';
        $embedding1 = [0.1, 0.2, 0.3, 0.4, 0.5];

        $text2 = 'Another text';
        $embedding2 = [0.6, 0.7, 0.8, 0.9, 1.0];

        $embeddings = [
            $text1 => $embedding1,
            $text2 => $embedding2,
        ];

        $adapter = new FakeAdapter($embeddings);

        $result1 = $adapter->embed(new EmbedRequest([$text1]));
        $result2 = $adapter->embed(new EmbedRequest([$text2]));

        $this->assertSame([$embedding1], $result1->getVectors());
        $this->assertSame([$embedding2], $result2->getVectors());
    }

    public function testEmbedWithEmptyEmbedding(): void
    {
        $text = 'Empty embedding';
        $embedding = [];

        $embeddings = [
            $text => $embedding,
        ];

        $adapter = new FakeAdapter($embeddings);

        $result = $adapter->embed(new EmbedRequest([$text]));

        $this->assertSame([$embedding], $result->getVectors());
        $this->assertEmpty($result->getVectors()[0]);
    }

    public function testEmbedThrowsExceptionForUnknownText(): void
    {
        $knownText = 'Known text';
        $embedding = [0.1, 0.2, 0.3];

        $unknownText = 'Unknown text';

        $embeddings = [
            $knownText => $embedding,
        ];

        $adapter = new FakeAdapter($embeddings);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('Text "%s" not found in embeddings.', $unknownText));

        $adapter->embed(new EmbedRequest([$unknownText]));
    }

    public function testConstructWithEmptyEmbeddings(): void
    {
        $adapter = new FakeAdapter([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Text "any text" not found in embeddings.');

        $adapter->embed(new EmbedRequest(['any text']));
    }

    public function testEmbedWithSpecialCharacters(): void
    {
        $text = 'Special chars: !@#$%^&*()_+';
        $embedding = [0.1, 0.2, 0.3];

        $embeddings = [
            $text => $embedding,
        ];

        $adapter = new FakeAdapter($embeddings);

        $result = $adapter->embed(new EmbedRequest([$text]));

        $this->assertSame([$embedding], $result->getVectors());
    }

    public function testEmbedCaseSensitivity(): void
    {
        $lowerCaseText = 'hello world';
        $lowerCaseEmbedding = [0.1, 0.2, 0.3];

        $upperCaseText = 'HELLO WORLD';
        $upperCaseEmbedding = [0.4, 0.5, 0.6];

        $embeddings = [
            $lowerCaseText => $lowerCaseEmbedding,
            $upperCaseText => $upperCaseEmbedding,
        ];

        $adapter = new FakeAdapter($embeddings);

        $lowerCaseResult = $adapter->embed(new EmbedRequest([$lowerCaseText]));
        $upperCaseResult = $adapter->embed(new EmbedRequest([$upperCaseText]));

        $this->assertSame([$lowerCaseEmbedding], $lowerCaseResult->getVectors());
        $this->assertSame([$upperCaseEmbedding], $upperCaseResult->getVectors());
        $this->assertNotSame($lowerCaseResult, $upperCaseResult);
    }
}
