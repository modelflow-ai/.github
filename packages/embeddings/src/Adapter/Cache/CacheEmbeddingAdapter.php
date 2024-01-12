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

namespace ModelflowAi\Embeddings\Adapter\Cache;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private readonly EmbeddingAdapterInterface $adapter,
        private readonly CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public function embedText(string $text): array
    {
        $hash = \hash('sha256', $text);
        $cacheItem = $this->cacheItemPool->getItem($hash);
        if ($cacheItem->isHit()) {
            /** @var float[] $result */
            $result = $cacheItem->get();

            return $result;
        }

        $result = $this->adapter->embedText($text);
        $cacheItem->set($result);
        $this->cacheItemPool->save($cacheItem);

        return $result;
    }
}
