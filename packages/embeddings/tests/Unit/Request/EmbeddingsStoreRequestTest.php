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
use ModelflowAi\Embeddings\Request\EmbeddingsStoreRequest;
use ModelflowAi\Embeddings\Response\EmbeddingsStoreResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EmbeddingsStoreRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testConstruct(): void
    {
        $execute = fn (EmbeddingsStoreRequest $request) => $this->prophesize(EmbeddingsStoreResponse::class)->reveal();

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $headerGenerator = static fn () => ['test-header' => 'test-value'];

        $request = new EmbeddingsStoreRequest(
            $execute,
            $embeddings,
            $headerGenerator,
        );

        $this->assertSame($embeddings, $request->getEmbeddings());
        $this->assertSame($headerGenerator, $request->getHeaderGenerator());
    }

    public function testConstructDefaultValues(): void
    {
        $execute = fn (EmbeddingsStoreRequest $request) => $this->prophesize(EmbeddingsStoreResponse::class)->reveal();

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $request = new EmbeddingsStoreRequest(
            $execute,
            $embeddings,
        );

        $this->assertSame($embeddings, $request->getEmbeddings());
        $this->assertNull($request->getHeaderGenerator());
    }

    public function testWithHeaderGenerator(): void
    {
        $execute = fn (EmbeddingsStoreRequest $request) => $this->prophesize(EmbeddingsStoreResponse::class)->reveal();

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $request = new EmbeddingsStoreRequest(
            $execute,
            $embeddings,
        );

        $headerGenerator = static fn () => ['new-header' => 'new-value'];

        $newRequest = $request->withHeaderGenerator($headerGenerator);

        $this->assertNotSame($request, $newRequest);
        $this->assertNull($request->getHeaderGenerator());
        $this->assertSame($headerGenerator, $newRequest->getHeaderGenerator());
    }

    public function testWithNullHeaderGenerator(): void
    {
        $execute = fn (EmbeddingsStoreRequest $request) => $this->prophesize(EmbeddingsStoreResponse::class)->reveal();

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $headerGenerator = static fn () => ['test-header' => 'test-value'];

        $request = new EmbeddingsStoreRequest(
            $execute,
            $embeddings,
            $headerGenerator,
        );

        $newRequest = $request->withHeaderGenerator(null);

        $this->assertNotSame($request, $newRequest);
        $this->assertNotNull($request->getHeaderGenerator());
        $this->assertNull($newRequest->getHeaderGenerator());
    }

    public function testExecute(): void
    {
        $mockResponse = new EmbeddingsStoreResponse(
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

        $execute = static function (EmbeddingsStoreRequest $request) use (&$called, $mockResponse) {
            $called = true;

            return $mockResponse;
        };

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $request = new EmbeddingsStoreRequest(
            $execute,
            $embeddings,
        );

        $response = $request->execute();

        $this->assertTrue($called);
        $this->assertSame($mockResponse, $response);
    }

    public function testExecuteWrongReturn(): void
    {
        $this->expectException(\RuntimeException::class);

        $mockResponse = new \stdClass();

        $execute = static fn (EmbeddingsStoreRequest $request) => $mockResponse;

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $request = new EmbeddingsStoreRequest(
            $execute,
            $embeddings,
        );

        $request->execute();
    }
}
