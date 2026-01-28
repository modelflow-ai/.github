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

    public function testEmbedReturnsFromCacheOnHit(): void
    {
        $text = 'Test text to embed';
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

        $this->cacheItemPool->getItem(Argument::any())->willReturn($cacheItem->reveal());

        // The adapter should not be called when cache hits
        $this->adapter->embed(Argument::any())->shouldNotBeCalled();

        $request = new EmbedRequest([$text]);
        $response = $this->cacheEmbeddingAdapter->embed($request);

        $this->assertSame([$expectedVector], $response->getVectors());
        $this->assertSame(10, $response->getUsage()->getPromptTokens());
        $this->assertSame(15, $response->getUsage()->getTotalTokens());
    }

    public function testEmbedCallsAdapterAndStoresCacheOnMiss(): void
    {
        $text = 'Test text to embed';
        $expectedVector = [0.1, 0.2, 0.3, 0.4, 0.5];
        $usage = new EmbeddingUsage(10, 15);

        $expectedResponse = new EmbedResponse(
            [$expectedVector],
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

        $this->cacheItemPool->getItem(Argument::any())->willReturn($cacheItem->reveal());
        $this->cacheItemPool->save($cacheItem->reveal())->shouldBeCalled();

        $this->adapter->embed(Argument::that(static fn ($request) => $request instanceof EmbedRequest
            && $request->getTexts() === [$text]))->willReturn($expectedResponse);

        $request = new EmbedRequest([$text]);
        $response = $this->cacheEmbeddingAdapter->embed($request);

        $this->assertSame([$expectedVector], $response->getVectors());
        // Token distribution: 10 prompt tokens for 1 text = 10, 15 total tokens for 1 text = 15
        $this->assertSame(10, $response->getUsage()->getPromptTokens());
        $this->assertSame(15, $response->getUsage()->getTotalTokens());
    }

    public function testEmbedWithDifferentInputsGeneratesDifferentCacheKeys(): void
    {
        $text1 = 'First text';
        $text2 = 'Second text';

        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.4, 0.5, 0.6];
        $usage1 = new EmbeddingUsage(5, 10);
        $usage2 = new EmbeddingUsage(6, 12);

        $response1 = new EmbedResponse([$vector1], $usage1);
        $response2 = new EmbedResponse([$vector2], $usage2);

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
        $cacheItem1->set(Argument::any())->will(static fn () => $cacheItem1->reveal());

        $cacheItem2 = $this->prophesize(CacheItemInterface::class);
        $cacheItem2->isHit()->willReturn(false);
        $cacheItem2->set(Argument::any())->will(static fn () => $cacheItem2->reveal());

        $this->cacheItemPool->getItem(Argument::any())->will(static function ($args) use ($cacheItem1, $cacheItem2) {
            static $callCount = 0;
            ++$callCount;

            return 1 === $callCount ? $cacheItem1->reveal() : $cacheItem2->reveal();
        });
        $this->cacheItemPool->save(Argument::any())->shouldBeCalled();

        $this->adapter->embed(Argument::that(static fn ($request) => $request instanceof EmbedRequest
            && $request->getTexts() === [$text1]))->willReturn($response1);

        $this->adapter->embed(Argument::that(static fn ($request) => $request instanceof EmbedRequest
            && $request->getTexts() === [$text2]))->willReturn($response2);

        $request1 = new EmbedRequest([$text1]);
        $request2 = new EmbedRequest([$text2]);

        $result1 = $this->cacheEmbeddingAdapter->embed($request1);
        $result2 = $this->cacheEmbeddingAdapter->embed($request2);

        $this->assertSame([$vector1], $result1->getVectors());
        $this->assertSame([$vector2], $result2->getVectors());
    }

    public function testEmbedBatchWithMixedCacheHitsAndMisses(): void
    {
        $texts = ['cached text', 'uncached text 1', 'uncached text 2'];

        $cachedVector = [0.1, 0.2, 0.3];
        $uncachedVector1 = [0.4, 0.5, 0.6];
        $uncachedVector2 = [0.7, 0.8, 0.9];

        // Cache hit for first text
        $cachedData = [
            'vector' => $cachedVector,
            'usage' => ['promptTokens' => 10, 'totalTokens' => 15],
        ];
        $cacheItem0 = $this->prophesize(CacheItemInterface::class);
        $cacheItem0->isHit()->willReturn(true);
        $cacheItem0->get()->willReturn($cachedData);

        // Cache miss for second text
        $cacheItem1 = $this->prophesize(CacheItemInterface::class);
        $cacheItem1->isHit()->willReturn(false);
        $cacheItem1->set(Argument::any())->will(static fn () => $cacheItem1->reveal());

        // Cache miss for third text
        $cacheItem2 = $this->prophesize(CacheItemInterface::class);
        $cacheItem2->isHit()->willReturn(false);
        $cacheItem2->set(Argument::any())->will(static fn () => $cacheItem2->reveal());

        // getItem is called twice per uncached text (once for check, once for set)
        // First 3 calls are for cache checks, next 2 are for cache sets
        $getItemCallSequence = [
            $cacheItem0->reveal(), // Check for 'cached text' - hit
            $cacheItem1->reveal(), // Check for 'uncached text 1' - miss
            $cacheItem2->reveal(), // Check for 'uncached text 2' - miss
            $cacheItem1->reveal(), // Set 'uncached text 1'
            $cacheItem2->reveal(), // Set 'uncached text 2'
        ];

        $callIndex = 0;
        $this->cacheItemPool->getItem(Argument::any())->will(static function ($args) use (&$getItemCallSequence, &$callIndex) {
            $item = $getItemCallSequence[$callIndex] ?? \end($getItemCallSequence);
            ++$callIndex;

            return $item;
        });
        $this->cacheItemPool->save(Argument::any())->shouldBeCalled();

        // Adapter should only be called for uncached texts
        $batchResponse = new EmbedResponse(
            [$uncachedVector1, $uncachedVector2],
            new EmbeddingUsage(20, 40),
        );
        $this->adapter->embed(Argument::that(static fn ($request) => $request instanceof EmbedRequest
            && $request->getTexts() === ['uncached text 1', 'uncached text 2']))->willReturn($batchResponse);

        $request = new EmbedRequest($texts);
        $response = $this->cacheEmbeddingAdapter->embed($request);

        // Should return vectors in correct order
        $this->assertSame([$cachedVector, $uncachedVector1, $uncachedVector2], $response->getVectors());
        $this->assertSame(3, $response->count());
    }
}
