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
use ModelflowAi\Embeddings\Generator\EmbeddingGeneratorInterface;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Request\EmbeddingsStoreRequest;
use ModelflowAi\Embeddings\Response\EmbeddingsStoreResponse;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;

class EmbeddingsStoreHandler implements EmbeddingsStoreHandlerInterface
{
    /**
     * @param array<string, EmbeddingGeneratorInterface> $embeddingGenerators
     * @param array<string, EmbeddingsStoreInterface> $embeddingStores
     * @param array<string, EmbeddingAdapterInterface> $embeddingAdapters
     * @param array<class-string<EmbeddingInterface>, string> $embeddingClassMapping Mapping from embedding class to store/generator/adapter key
     */
    public function __construct(
        private readonly array $embeddingGenerators,
        private readonly array $embeddingStores,
        private readonly array $embeddingAdapters,
        private readonly array $embeddingClassMapping,
    ) {
    }

    public function handle(EmbeddingsStoreRequest $request): EmbeddingsStoreResponse
    {
        $processedEmbeddings = [];
        $embeddingsPerKey = [];
        $usages = [];
        foreach ($request->getEmbeddings() as $embedding) {
            $embeddingClass = $embedding::class;
            if (!isset($this->embeddingClassMapping[$embeddingClass])) {
                throw new \InvalidArgumentException(
                    \sprintf('No mapping configured for embedding class "%s".', $embeddingClass),
                );
            }

            $key = $this->embeddingClassMapping[$embeddingClass];

            $embeddingGenerator = $this->getEmbeddingGenerator($key);
            $embeddingAdapter = $this->getEmbeddingAdapter($key);

            $embeddingsPerKey[$key] ??= [];

            $generatedEmbeddings = $embeddingGenerator->generateEmbedding(
                $embedding,
                $request->getHeaderGenerator(),
            );
            foreach ($generatedEmbeddings as $generatedEmbedding) {
                $embedRequest = new EmbedRequest($generatedEmbedding->getContent());
                $embedResponse = $embeddingAdapter->embed($embedRequest);
                $generatedEmbedding->setVector($embedResponse->getVector());
                $usages[] = $embedResponse->getUsage();
                $embeddingsPerKey[$key][] = $generatedEmbedding;
                $processedEmbeddings[] = $generatedEmbedding;
            }
        }

        foreach ($embeddingsPerKey as $key => $embeddings) {
            $embeddingStore = $this->getEmbeddingStore($key);
            $embeddingStore->addDocuments($embeddings);
        }

        return new EmbeddingsStoreResponse($processedEmbeddings, EmbeddingUsage::fromUsages($usages));
    }

    private function getEmbeddingGenerator(string $key): EmbeddingGeneratorInterface
    {
        if (!isset($this->embeddingGenerators[$key])) {
            throw new \InvalidArgumentException(\sprintf('No generator configured for key "%s".', $key));
        }

        /** @var EmbeddingGeneratorInterface $generator */
        $generator = $this->embeddingGenerators[$key];

        return $generator;
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
