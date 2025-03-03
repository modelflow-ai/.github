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
use PHPUnit\Framework\TestCase;
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

    public function testEmbedTextReturnsFromCacheOnHit(): void
    {
        $text = 'Test text to embed';
        $hash = \hash('sha256', $text);
        $expectedEmbedding = [0.1, 0.2, 0.3, 0.4, 0.5];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(true);
        $cacheItem->get()->willReturn($expectedEmbedding);

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());

        // The adapter should not be called when cache hits
        $this->adapter->embedText($text)->shouldNotBeCalled();

        $result = $this->cacheEmbeddingAdapter->embedText($text);

        $this->assertSame($expectedEmbedding, $result);
    }

    public function testEmbedTextCallsAdapterAndStoresCacheOnMiss(): void
    {
        $text = 'Test text to embed';
        $hash = \hash('sha256', $text);
        $expectedEmbedding = [0.1, 0.2, 0.3, 0.4, 0.5];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(false);
        $cacheItem->set($expectedEmbedding)->shouldBeCalled()->willReturn($cacheItem->reveal());
        $cacheItem->get()->shouldNotBeCalled();

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());
        $this->cacheItemPool->save($cacheItem->reveal())->shouldBeCalled();

        $this->adapter->embedText($text)->willReturn($expectedEmbedding);

        $result = $this->cacheEmbeddingAdapter->embedText($text);

        $this->assertSame($expectedEmbedding, $result);
    }

    public function testEmbedTextWithDifferentInputsGeneratesDifferentCacheKeys(): void
    {
        $text1 = 'First text';
        $text2 = 'Second text';
        $hash1 = \hash('sha256', $text1);
        $hash2 = \hash('sha256', $text2);

        $embedding1 = [0.1, 0.2, 0.3];
        $embedding2 = [0.4, 0.5, 0.6];

        $cacheItem1 = $this->prophesize(CacheItemInterface::class);
        $cacheItem1->isHit()->willReturn(false);
        $cacheItem1->set($embedding1)->willReturn($cacheItem1->reveal());

        $cacheItem2 = $this->prophesize(CacheItemInterface::class);
        $cacheItem2->isHit()->willReturn(false);
        $cacheItem2->set($embedding2)->willReturn($cacheItem2->reveal());

        $this->cacheItemPool->getItem($hash1)->willReturn($cacheItem1->reveal());
        $this->cacheItemPool->save($cacheItem1->reveal())->shouldBeCalled();

        $this->cacheItemPool->getItem($hash2)->willReturn($cacheItem2->reveal());
        $this->cacheItemPool->save($cacheItem2->reveal())->shouldBeCalled();

        $this->adapter->embedText($text1)->willReturn($embedding1);
        $this->adapter->embedText($text2)->willReturn($embedding2);

        $result1 = $this->cacheEmbeddingAdapter->embedText($text1);
        $result2 = $this->cacheEmbeddingAdapter->embedText($text2);

        $this->assertSame($embedding1, $result1);
        $this->assertSame($embedding2, $result2);
        $this->assertNotSame($hash1, $hash2);
    }

    public function testEmbedTextWithEmptyString(): void
    {
        $text = '';
        $hash = \hash('sha256', $text);
        $expectedEmbedding = [];

        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(false);
        $cacheItem->set($expectedEmbedding)->willReturn($cacheItem->reveal());

        $this->cacheItemPool->getItem($hash)->willReturn($cacheItem->reveal());
        $this->cacheItemPool->save($cacheItem->reveal())->shouldBeCalled();

        $this->adapter->embedText($text)->willReturn($expectedEmbedding);

        $result = $this->cacheEmbeddingAdapter->embedText($text);

        $this->assertSame($expectedEmbedding, $result);
    }
}
