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

namespace ModelflowAi\Embeddings\Tests\Unit\Request;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Model\EmbeddingTrait;
use ModelflowAi\Embeddings\Request\EmbeddingsSimilarityRequest;
use ModelflowAi\Embeddings\Response\EmbeddingsSimilarityResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EmbeddingsSimilarityRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testConstruct(): void
    {
        $execute = fn (EmbeddingsSimilarityRequest $request) => $this->prophesize(EmbeddingsSimilarityResponse::class)
            ->reveal();

        $request = new EmbeddingsSimilarityRequest(
            $execute,
            'test content',
            'test_key',
            5,
            [
                'test1' => 'value1',
                'test2' => 'value2',
            ],
        );

        $this->assertSame('test content', $request->getContent());
        $this->assertSame('test_key', $request->getKey());
        $this->assertSame(5, $request->getLimit());
        $this->assertSame([
            'test1' => 'value1',
            'test2' => 'value2',
        ], $request->getAdditionalFilter());
    }

    public function testConstructDefaultValues(): void
    {
        $execute = fn (EmbeddingsSimilarityRequest $request) => $this->prophesize(EmbeddingsSimilarityResponse::class)
            ->reveal();

        $request = new EmbeddingsSimilarityRequest(
            $execute,
            'test content',
            'test_key',
        );

        $this->assertSame('test content', $request->getContent());
        $this->assertSame('test_key', $request->getKey());
        $this->assertSame(1, $request->getLimit());
        $this->assertSame([], $request->getAdditionalFilter());
    }

    public function testWithLimit(): void
    {
        $execute = fn (EmbeddingsSimilarityRequest $request) => $this->prophesize(EmbeddingsSimilarityResponse::class)
            ->reveal();

        $request = new EmbeddingsSimilarityRequest(
            $execute,
            'test content',
            'test_key',
        );

        $newRequest = $request->withLimit(10);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame(1, $request->getLimit());
        $this->assertSame(10, $newRequest->getLimit());
    }

    public function testWithAdditionalFilter(): void
    {
        $execute = fn (EmbeddingsSimilarityRequest $request) => $this->prophesize(EmbeddingsSimilarityResponse::class)
            ->reveal();

        $request = new EmbeddingsSimilarityRequest(
            $execute,
            'test content',
            'test_key',
        );

        $newRequest = $request->withAdditionalFilter([
            'test1' => 'value1',
            'test2' => 'value2',
        ]);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame([], $request->getAdditionalFilter());
        $this->assertSame([
            'test1' => 'value1',
            'test2' => 'value2',
        ], $newRequest->getAdditionalFilter());
    }

    public function testExecute(): void
    {
        $mockResponse = new EmbeddingsSimilarityResponse(
            [
                new class implements EmbeddingInterface {
                    use EmbeddingTrait;

                    /**
                     * @return string[]
                     */
                    public function getIdentifierParts(): array
                    {
                        return [];
                    }
                },
            ],
            EmbeddingUsage::empty(),
        );
        $called = false;

        $execute = static function (EmbeddingsSimilarityRequest $request) use (&$called, $mockResponse) {
            $called = true;

            return $mockResponse;
        };

        $request = new EmbeddingsSimilarityRequest(
            $execute,
            'test content',
            'test_key',
        );

        $response = $request->execute();

        $this->assertTrue($called);
        $this->assertSame($mockResponse, $response);
    }

    public function testExecuteWrongReturn(): void
    {
        $this->expectException(\RuntimeException::class);

        $mockResponse = new \stdClass();

        $execute = static fn (EmbeddingsSimilarityRequest $request) => $mockResponse;

        $request = new EmbeddingsSimilarityRequest(
            $execute,
            'test content',
            'test_key',
        );

        $request->execute();
    }
}
