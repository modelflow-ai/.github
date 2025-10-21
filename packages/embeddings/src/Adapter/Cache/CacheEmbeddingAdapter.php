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

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheEmbeddingAdapter implements EmbeddingAdapterInterface
{
    private string $cachePrefix;

    public function __construct(
        private EmbeddingAdapterInterface $adapter,
        private CacheItemPoolInterface $cacheItemPool,
        ?string $cachePrefix = null,
    ) {
        // Generate cache prefix from adapter class name if not provided
        $this->cachePrefix = $cachePrefix ?? \str_replace('\\', '_', $adapter::class);
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $texts = $request->getTexts();
        $uncachedTexts = [];
        $uncachedIndices = [];
        $vectors = [];
        $totalPromptTokens = 0;
        $totalTotalTokens = 0;

        // Check cache for each text
        foreach ($texts as $index => $text) {
            $hash = $this->getCacheKey($text);
            $cacheItem = $this->cacheItemPool->getItem($hash);

            if ($cacheItem->isHit()) {
                /** @var array{vector: float[], usage: array{promptTokens: int, totalTokens: int}} $cachedData */
                $cachedData = $cacheItem->get();
                $vectors[$index] = $cachedData['vector'];
                $totalPromptTokens += $cachedData['usage']['promptTokens'];
                $totalTotalTokens += $cachedData['usage']['totalTokens'];
            } else {
                $uncachedTexts[] = $text;
                $uncachedIndices[] = $index;
            }
        }

        // Batch process uncached texts
        if ([] !== $uncachedTexts) {
            $batchRequest = new EmbedRequest($uncachedTexts);
            $batchResponse = $this->adapter->embed($batchRequest);
            $uncachedVectors = $batchResponse->getVectors();

            // Cache and assign results
            $uncachedCount = \count($uncachedTexts);
            $batchPromptTokens = $batchResponse->getUsage()->getPromptTokens();
            $batchTotalTokens = $batchResponse->getUsage()->getTotalTokens();

            // Calculate base tokens per text and remainders
            $basePromptTokens = (int) ($batchPromptTokens / $uncachedCount);
            $baseTokens = (int) ($batchTotalTokens / $uncachedCount);
            $remainderPromptTokens = $batchPromptTokens % $uncachedCount;
            $remainderTokens = $batchTotalTokens % $uncachedCount;

            foreach ($uncachedTexts as $i => $text) {
                $hash = $this->getCacheKey($text);
                $cacheItem = $this->cacheItemPool->getItem($hash);

                // Distribute remainder tokens to first N texts to maintain accuracy
                $promptTokens = $basePromptTokens + ($i < $remainderPromptTokens ? 1 : 0);
                $totalTokens = $baseTokens + ($i < $remainderTokens ? 1 : 0);

                $cacheData = [
                    'vector' => $uncachedVectors[$i],
                    'usage' => [
                        'promptTokens' => $promptTokens,
                        'totalTokens' => $totalTokens,
                    ],
                ];

                $cacheItem->set($cacheData);
                $this->cacheItemPool->save($cacheItem);

                // Assign to correct position in final array
                $originalIndex = $uncachedIndices[$i];
                $vectors[$originalIndex] = $uncachedVectors[$i];
                $totalPromptTokens += $promptTokens;
                $totalTotalTokens += $totalTokens;
            }
        }

        // Ensure vectors are in correct order
        \ksort($vectors);

        return new EmbedResponse(
            \array_values($vectors),
            new EmbeddingUsage($totalPromptTokens, $totalTotalTokens),
        );
    }

    /**
     * Generate cache key with adapter namespace to prevent cross-model collisions.
     */
    private function getCacheKey(string $text): string
    {
        return \hash('sha256', $this->cachePrefix . ':' . $text);
    }
}
