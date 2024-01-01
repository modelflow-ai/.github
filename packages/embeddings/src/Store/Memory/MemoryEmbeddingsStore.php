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

namespace ModelflowAi\Embeddings\Store\Memory;

use ModelflowAi\Embeddings\Algorithm\DistanceL2Utils;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MemoryEmbeddingsStore implements EmbeddingsStoreInterface
{
    /**
     * @var EmbeddingInterface[]
     */
    private array $embeddings = [];

    public function addDocument(EmbeddingInterface $embedding): void
    {
        $this->embeddings[] = $embedding;
    }

    public function addDocuments(array $embeddings): void
    {
        foreach ($embeddings as $embedding) {
            $this->addDocument($embedding);
        }
    }

    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array
    {
        $distances = [];

        $accessor = new PropertyAccessor();

        foreach ($this->embeddings as $index => $embedding) {
            foreach ($additionalArguments as $key => $value) {
                if ($accessor->getValue($embedding, $key) !== $value) {
                    continue 2;
                }
            }

            if (null === $embedding->getVector()) {
                continue;
            }

            $dist = DistanceL2Utils::euclideanDistanceL2($vector, $embedding->getVector());
            $distances[$index] = $dist;
        }

        \asort($distances); // Sort by distance (ascending).

        $topKIndices = \array_slice(\array_keys($distances), 0, $k, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = $this->embeddings[$index];
        }

        return $results;
    }
}
