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
        $text = $request->getText();
        $hash = \hash('sha256', $text);
        $cacheItem = $this->cacheItemPool->getItem($hash);

        if ($cacheItem->isHit()) {
            /** @var array{vector: float[], usage: array{promptTokens: int, totalTokens: int}} $cachedData */
            $cachedData = $cacheItem->get();
            $usage = new EmbeddingUsage(
                $cachedData['usage']['promptTokens'],
                $cachedData['usage']['totalTokens'],
            );

            return new EmbedResponse($cachedData['vector'], $usage);
        }

        $response = $this->adapter->embed($request);
        $vector = $response->getVector();
        $usage = $response->getUsage();

        $cacheData = [
            'vector' => $vector,
            'usage' => [
                'promptTokens' => $usage->getPromptTokens(),
                'totalTokens' => $usage->getTotalTokens(),
            ],
        ];

        $cacheItem->set($cacheData);
        $this->cacheItemPool->save($cacheItem);

        return $response;
    }
}
