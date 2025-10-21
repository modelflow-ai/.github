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
     * @param iterable<string, EmbeddingGeneratorInterface> $embeddingGenerators
     * @param iterable<string, EmbeddingsStoreInterface> $embeddingStores
     * @param iterable<string, EmbeddingAdapterInterface> $embeddingAdapters
     * @param array<class-string<EmbeddingInterface>, string> $embeddingClassMapping Mapping from embedding class to store/generator/adapter key
     */
    public function __construct(
        private iterable $embeddingGenerators,
        private iterable $embeddingStores,
        private iterable $embeddingAdapters,
        private array $embeddingClassMapping,
    ) {
        $this->embeddingGenerators = $embeddingGenerators instanceof \Traversable ? \iterator_to_array($embeddingGenerators) : $embeddingGenerators;
        $this->embeddingStores = $embeddingStores instanceof \Traversable ? \iterator_to_array($embeddingStores) : $embeddingStores;
        $this->embeddingAdapters = $embeddingAdapters instanceof \Traversable ? \iterator_to_array($embeddingAdapters) : $embeddingAdapters;
    }

    public function handle(EmbeddingsStoreRequest $request): EmbeddingsStoreResponse
    {
        // Step 1: Generate all embeddings and group by adapter key
        $embeddingsByAdapter = [];
        $embeddingsPerKey = [];

        foreach ($request->getEmbeddings() as $embedding) {
            $embeddingClass = $embedding::class;
            if (!isset($this->embeddingClassMapping[$embeddingClass])) {
                throw new \InvalidArgumentException(\sprintf('No mapping configured for embedding class "%s".', $embeddingClass));
            }

            $key = $this->embeddingClassMapping[$embeddingClass];
            $embeddingGenerator = $this->getEmbeddingGenerator($key);

            $generatedEmbeddings = $embeddingGenerator->generateEmbedding(
                $embedding,
                $request->getHeaderGenerator(),
            );

            foreach ($generatedEmbeddings as $generatedEmbedding) {
                $embeddingsByAdapter[$key][] = $generatedEmbedding;
            }

            $embeddingsPerKey[$key] ??= [];
        }

        // Step 2: Process each adapter's embeddings in batches
        $processedEmbeddings = [];
        $usages = [];
        $batchSize = 30;

        foreach ($embeddingsByAdapter as $key => $generatedEmbeddings) {
            $embeddingAdapter = $this->getEmbeddingAdapter($key);
            $chunks = \array_chunk($generatedEmbeddings, $batchSize);

            foreach ($chunks as $chunk) {
                // Collect texts for batch processing
                $texts = [];
                foreach ($chunk as $generatedEmbedding) {
                    $texts[] = $generatedEmbedding->getContent();
                }

                // Send batch request
                $embedRequest = new EmbedRequest($texts);
                $embedResponse = $embeddingAdapter->embed($embedRequest);
                $vectors = $embedResponse->getVectors();

                // Verify vector count matches input count to prevent silent misalignment
                if (\count($vectors) !== \count($chunk)) {
                    throw new \RuntimeException(\sprintf(
                        'Vector count mismatch: expected %d vectors but got %d from adapter.',
                        \count($chunk),
                        \count($vectors),
                    ));
                }

                // Assign vectors back to embeddings
                foreach ($chunk as $index => $generatedEmbedding) {
                    $generatedEmbedding->setVector($vectors[$index]);
                    $embeddingsPerKey[$key][] = $generatedEmbedding;
                    $processedEmbeddings[] = $generatedEmbedding;
                }

                $usages[] = $embedResponse->getUsage();
            }
        }

        // Step 3: Store embeddings
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
