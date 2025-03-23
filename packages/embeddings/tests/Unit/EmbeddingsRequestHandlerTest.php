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

namespace ModelflowAi\Embeddings\Tests\Unit;

use ModelflowAi\Embeddings\EmbeddingsRequestHandler;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandlerInterface;
use ModelflowAi\Embeddings\Handler\EmbeddingsStoreHandlerInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Request\EmbeddingsSimilarityRequest;
use ModelflowAi\Embeddings\Request\EmbeddingsStoreRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EmbeddingsRequestHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateStoreRequest(): void
    {
        $storeHandler = $this->prophesize(EmbeddingsStoreHandlerInterface::class);
        $similarityHandler = $this->prophesize(EmbeddingsSimilarityHandlerInterface::class);

        $embeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $handler = new EmbeddingsRequestHandler(
            $storeHandler->reveal(),
            $similarityHandler->reveal(),
        );

        $request = $handler->createStoreRequest(...$embeddings);

        $this->assertInstanceOf(EmbeddingsStoreRequest::class, $request);
        $this->assertSame($embeddings, $request->getEmbeddings());
    }

    public function testCreateSimilarityRequest(): void
    {
        $storeHandler = $this->prophesize(EmbeddingsStoreHandlerInterface::class);
        $similarityHandler = $this->prophesize(EmbeddingsSimilarityHandlerInterface::class);

        $handler = new EmbeddingsRequestHandler(
            $storeHandler->reveal(),
            $similarityHandler->reveal(),
        );

        $request = $handler->createSimilarityRequest('test content', 'test_key');

        $this->assertInstanceOf(EmbeddingsSimilarityRequest::class, $request);
        $this->assertSame('test content', $request->getContent());
        $this->assertSame('test_key', $request->getKey());
    }
}
