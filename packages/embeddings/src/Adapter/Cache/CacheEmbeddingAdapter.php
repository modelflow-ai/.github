<?php

namespace ModelflowAi\Embeddings\Adapter\Cache;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private EmbeddingAdapterInterface $adapter,
        private CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public function embedText(string $text): array
    {
        $hash = hash('sha256', $text);
        $cacheItem = $this->cacheItemPool->getItem($hash);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = $this->adapter->embedText($text);
        $cacheItem->set($result);
        $this->cacheItemPool->save($cacheItem);

        return $result;
    }
}
