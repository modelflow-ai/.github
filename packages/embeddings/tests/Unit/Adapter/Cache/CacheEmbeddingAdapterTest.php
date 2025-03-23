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

namespace ModelflowAi\Embeddings\Tests\Unit\Adapter\Cache;

use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheEmbeddingAdapterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<EmbeddingAdapterInterface>
     */
    private ObjectProphecy $adapter;

    /**
     * @var ObjectProphecy<CacheItemPoolInterface>
     */
    private ObjectProphecy $cacheItemPool;

    private CacheEmbeddingAdapter $cacheEmbeddingAdapter;

    protected function setUp(): void
    {
        $this->adapter = $this->prophesize(EmbeddingAdapterInterface::class);
        $this->cacheItemPool = $this->prophesize(CacheItemPoolInterface::class);

        $this->cacheEmbeddingAdapter = new CacheEmbeddingAdapter(
            $this->adapter->reveal(),
            $this->cacheItemPool->reveal(),
        );
    }

    public function testEmbedText(): void
    {
        $text = 'Test text to embed';
        $hash = \hash('sha256', $text);
        $expectedVector = [0.1, 0.2, 0.3, 0.4, 0.5];
        $usage = new EmbeddingUsage(10, 15);

        $expectedResponse = new EmbedResponse(
            $expectedVector,
            $usage,
        );

        $expectedCacheData = [
            'vector' => $expectedVector,
            'usage' => [
                'promptTokens' => 10,
                'totalTokens' => 15,
            ],
        ];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(false);
        $cacheItem->set($expectedCacheData)->shouldBeCalled()->willReturn($cacheItem->reveal());
        $cacheItem->get()->shouldNotBeCalled();

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());
        $this->cacheItemPool->save($cacheItem->reveal())->shouldBeCalled();

        $this->adapter->embed(Argument::that(fn ($request) => $request instanceof EmbedRequest
            && $request->getText() === $text))->willReturn($expectedResponse);

        $request = new EmbedRequest($text);
        $response = $this->cacheEmbeddingAdapter->embedText($request->getText());

        $this->assertSame($expectedVector, $response);
    }

    public function testEmbedReturnsFromCacheOnHit(): void
    {
        $text = 'Test text to embed';
        $hash = \hash('sha256', $text);
        $expectedVector = [0.1, 0.2, 0.3, 0.4, 0.5];

        $cachedData = [
            'vector' => $expectedVector,
            'usage' => [
                'promptTokens' => 10,
                'totalTokens' => 15,
            ],
        ];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(true);
        $cacheItem->get()->willReturn($cachedData);

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());

        // The adapter should not be called when cache hits
        $this->adapter->embed(Argument::any())->shouldNotBeCalled();

        $request = new EmbedRequest($text);
        $response = $this->cacheEmbeddingAdapter->embed($request);

        $this->assertSame($expectedVector, $response->getVector());
        $this->assertSame(10, $response->getUsage()->getPromptTokens());
        $this->assertSame(15, $response->getUsage()->getTotalTokens());
    }

    public function testEmbedCallsAdapterAndStoresCacheOnMiss(): void
    {
        $text = 'Test text to embed';
        $hash = \hash('sha256', $text);
        $expectedVector = [0.1, 0.2, 0.3, 0.4, 0.5];
        $usage = new EmbeddingUsage(10, 15);

        $expectedResponse = new EmbedResponse(
            $expectedVector,
            $usage,
        );

        $expectedCacheData = [
            'vector' => $expectedVector,
            'usage' => [
                'promptTokens' => 10,
                'totalTokens' => 15,
            ],
        ];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(false);
        $cacheItem->set($expectedCacheData)->shouldBeCalled()->willReturn($cacheItem->reveal());
        $cacheItem->get()->shouldNotBeCalled();

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());
        $this->cacheItemPool->save($cacheItem->reveal())->shouldBeCalled();

        $this->adapter->embed(Argument::that(fn ($request) => $request instanceof EmbedRequest
            && $request->getText() === $text))->willReturn($expectedResponse);

        $request = new EmbedRequest($text);
        $response = $this->cacheEmbeddingAdapter->embed($request);

        $this->assertSame($expectedVector, $response->getVector());
        $this->assertSame($usage, $response->getUsage());
    }

    public function testEmbedWithDifferentInputsGeneratesDifferentCacheKeys(): void
    {
        $text1 = 'First text';
        $text2 = 'Second text';
        $hash1 = \hash('sha256', $text1);
        $hash2 = \hash('sha256', $text2);

        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.4, 0.5, 0.6];
        $usage1 = new EmbeddingUsage(5, 10);
        $usage2 = new EmbeddingUsage(6, 12);

        $response1 = new EmbedResponse($vector1, $usage1);
        $response2 = new EmbedResponse($vector2, $usage2);

        $cacheData1 = [
            'vector' => $vector1,
            'usage' => [
                'promptTokens' => 5,
                'totalTokens' => 10,
            ],
        ];

        $cacheData2 = [
            'vector' => $vector2,
            'usage' => [
                'promptTokens' => 6,
                'totalTokens' => 12,
            ],
        ];

        $cacheItem1 = $this->prophesize(CacheItemInterface::class);
        $cacheItem1->isHit()->willReturn(false);
        $cacheItem1->set($cacheData1)->willReturn($cacheItem1->reveal());

        $cacheItem2 = $this->prophesize(CacheItemInterface::class);
        $cacheItem2->isHit()->willReturn(false);
        $cacheItem2->set($cacheData2)->willReturn($cacheItem2->reveal());

        $this->cacheItemPool->getItem($hash1)->willReturn($cacheItem1->reveal());
        $this->cacheItemPool->save($cacheItem1->reveal())->shouldBeCalled();

        $this->cacheItemPool->getItem($hash2)->willReturn($cacheItem2->reveal());
        $this->cacheItemPool->save($cacheItem2->reveal())->shouldBeCalled();

        $this->adapter->embed(Argument::that(fn ($request) => $request instanceof EmbedRequest
            && $request->getText() === $text1))->willReturn($response1);

        $this->adapter->embed(Argument::that(fn ($request) => $request instanceof EmbedRequest
            && $request->getText() === $text2))->willReturn($response2);

        $request1 = new EmbedRequest($text1);
        $request2 = new EmbedRequest($text2);

        $result1 = $this->cacheEmbeddingAdapter->embed($request1);
        $result2 = $this->cacheEmbeddingAdapter->embed($request2);

        $this->assertSame($vector1, $result1->getVector());
        $this->assertSame($vector2, $result2->getVector());
        $this->assertNotSame($hash1, $hash2);
    }

    public function testEmbedWithEmptyString(): void
    {
        $text = '';
        $hash = \hash('sha256', $text);
        $expectedVector = [];
        $usage = new EmbeddingUsage(0, 0);

        $response = new EmbedResponse(
            $expectedVector,
            $usage,
        );

        $expectedCacheData = [
            'vector' => $expectedVector,
            'usage' => [
                'promptTokens' => 0,
                'totalTokens' => 0,
            ],
        ];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(false);
        $cacheItem->set($expectedCacheData)->willReturn($cacheItem->reveal());

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());
        $this->cacheItemPool->save($cacheItem->reveal())->shouldBeCalled();

        $this->adapter->embed(Argument::that(fn ($request) => $request instanceof EmbedRequest
            && '' === $request->getText()))->willReturn($response);

        $request = new EmbedRequest($text);
        $result = $this->cacheEmbeddingAdapter->embed($request);

        $this->assertSame($expectedVector, $result->getVector());
    }
}
