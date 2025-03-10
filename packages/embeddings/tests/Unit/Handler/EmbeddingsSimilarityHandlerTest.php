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

namespace ModelflowAi\Embeddings\Tests\Unit\Handler;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Handler\EmbeddingsSimilarityHandler;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Request\EmbeddingsSimilarityRequest;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class EmbeddingsSimilarityHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandle(): void
    {
        $key = 'test_key';
        $content = 'test content';
        $limit = 3;
        $filter = [
            'test1' => 'value1',
            'test2' => 'value2',
        ];
        $vector = [0.1, 0.2, 0.3];

        $store = $this->prophesize(EmbeddingsStoreInterface::class);
        $adapter = $this->prophesize(EmbeddingAdapterInterface::class);

        $embedResponse = new EmbedResponse($vector, new EmbeddingUsage(10, 20));
        $adapter->embed(Argument::that(fn (EmbedRequest $request) => $request->getText() === $content))->willReturn($embedResponse);

        $similarEmbeddings = [
            $this->prophesize(EmbeddingInterface::class)->reveal(),
            $this->prophesize(EmbeddingInterface::class)->reveal(),
        ];

        $store->similaritySearch($vector, $limit, $filter)->willReturn($similarEmbeddings);

        $handler = new EmbeddingsSimilarityHandler(
            [$key => $store->reveal()],
            [$key => $adapter->reveal()],
        );

        $request = new EmbeddingsSimilarityRequest(
            function () {},
            $content,
            $key,
            $limit,
            $filter,
        );

        $response = $handler->handle($request);

        $this->assertSame($similarEmbeddings, $response->getEmbeddings());
        $this->assertSame(10, $response->getUsage()->getPromptTokens());
        $this->assertSame(20, $response->getUsage()->getTotalTokens());
    }

    public function testHandleWithInvalidStoreKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No store configured for key "invalid_key".');

        $handler = new EmbeddingsSimilarityHandler([], []);
        $request = new EmbeddingsSimilarityRequest(
            function () {},
            'content',
            'invalid_key',
        );

        $handler->handle($request);
    }

    public function testHandleWithInvalidAdapterKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No adapter configured for key "test_key".');

        $key = 'test_key';
        $store = $this->prophesize(EmbeddingsStoreInterface::class);

        $handler = new EmbeddingsSimilarityHandler(
            [$key => $store->reveal()],
            [],
        );

        $request = new EmbeddingsSimilarityRequest(
            function () {},
            'content',
            $key,
        );

        $handler->handle($request);
    }
}
