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
use ModelflowAi\Embeddings\Store\ScoreAssignmentTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MemoryEmbeddingsStore implements EmbeddingsStoreInterface
{
    use ScoreAssignmentTrait;
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
                if (\is_string($value)) {
                    if ($accessor->getValue($embedding, $key) !== $value) {
                        continue 2;
                    }
                } elseif (\is_array($value)) {
                    if (!\in_array($accessor->getValue($embedding, $key), $value, true)) {
                        continue 2;
                    }
                } else {
                    throw new \InvalidArgumentException('Unsupported filter value type. Expected string or array.');
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
            $embedding = clone $this->embeddings[$index];
            // Calculate similarity score (inverse of distance, normalized to 0-1 range)
            // Lower distance = higher similarity
            $distance = $distances[$index];
            $score = 1.0 / (1.0 + $distance);

            // Assign score using cached reflection helper
            $this->assignScore($embedding, $score);

            $results[] = $embedding;
        }

        return $results;
    }
}
