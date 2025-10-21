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

use ModelflowAi\Embeddings\Adapter\DeprecatedEmbedTextTrait;
use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheEmbeddingAdapter implements EmbeddingAdapterInterface
{
    use DeprecatedEmbedTextTrait;

    public function __construct(
        private EmbeddingAdapterInterface $adapter,
        private CacheItemPoolInterface $cacheItemPool,
    ) {
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
            $hash = \hash('sha256', $text);
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
            foreach ($uncachedTexts as $i => $text) {
                $hash = \hash('sha256', $text);
                $cacheItem = $this->cacheItemPool->getItem($hash);

                // Distribute usage evenly across uncached texts
                $promptTokens = (int) ($batchResponse->getUsage()->getPromptTokens() / \count($uncachedTexts));
                $totalTokens = (int) ($batchResponse->getUsage()->getTotalTokens() / \count($uncachedTexts));

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
}
