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

namespace ModelflowAi\Embeddings\Handler;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Request\EmbeddingsSimilarityRequest;
use ModelflowAi\Embeddings\Response\EmbeddingsSimilarityResponse;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;

class EmbeddingsSimilarityHandler implements EmbeddingsSimilarityHandlerInterface
{
    /**
     * @param iterable<string, EmbeddingsStoreInterface> $embeddingStores
     * @param iterable<string, EmbeddingAdapterInterface> $embeddingAdapters
     */
    public function __construct(
        private iterable $embeddingStores,
        private iterable $embeddingAdapters,
    ) {
        $this->embeddingStores = $embeddingStores instanceof \Traversable ? \iterator_to_array($embeddingStores) : $embeddingStores;
        $this->embeddingAdapters = $embeddingAdapters instanceof \Traversable ? \iterator_to_array($embeddingAdapters) : $embeddingAdapters;
    }

    public function handle(EmbeddingsSimilarityRequest $request): EmbeddingsSimilarityResponse
    {
        $key = $request->getKey();

        $embeddingStore = $this->getEmbeddingStore($key);
        $embeddingAdapter = $this->getEmbeddingAdapter($key);

        $embedRequest = new EmbedRequest([$request->getContent()]);
        $embedResponse = $embeddingAdapter->embed($embedRequest);
        $usage = $embedResponse->getUsage();

        $similarEmbeddings = $embeddingStore->similaritySearch(
            $embedResponse->getVectors()[0],
            $request->getLimit(),
            $request->getAdditionalFilter(),
        );

        return new EmbeddingsSimilarityResponse($similarEmbeddings, $usage);
    }

    private function getEmbeddingStore(string $key): EmbeddingsStoreInterface
    {
        if (!isset($this->embeddingStores[$key])) {
            throw new \InvalidArgumentException(\sprintf('No store configured for key "%s".', $key));
        }

        /** @var EmbeddingsStoreInterface $store */
        $store = $this->embeddingStores[$key];

        return $store;
    }

    private function getEmbeddingAdapter(string $key): EmbeddingAdapterInterface
    {
        if (!isset($this->embeddingAdapters[$key])) {
            throw new \InvalidArgumentException(\sprintf('No adapter configured for key "%s".', $key));
        }

        /** @var EmbeddingAdapterInterface $adapter */
        $adapter = $this->embeddingAdapters[$key];

        return $adapter;
    }
}
