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

namespace ModelflowAi\Embeddings\Store\Filesystem;

use ModelflowAi\Embeddings\Algorithm\DistanceL2Utils;
use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Store\EmbeddingsStoreInterface;
use ModelflowAi\Embeddings\Store\ScoreAssignmentTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Heavenly inspired by LLPhant
 * https://github.com/theodo-group/LLPhant/blob/main/src/Embeddings/VectorStores/FileSystem/FileSystemVectorStore.php.
 */
class FilesystemEmbeddingsStore implements EmbeddingsStoreInterface
{
    use ScoreAssignmentTrait;

    public function __construct(
        public string $filePath,
    ) {
    }

    public function addDocument(EmbeddingInterface $embedding): void
    {
        $embeddingsList = $this->readDocumentsFromFile();
        $embeddingsList[] = $embedding;
        $this->saveDocumentsToFile($embeddingsList);
    }

    public function addDocuments(array $embeddings): void
    {
        $embeddingsList = $this->readDocumentsFromFile();
        $embeddingsList = \array_merge($embeddingsList, $embeddings);
        $this->saveDocumentsToFile($embeddingsList);
    }

    public function similaritySearch(array $vector, int $k = 4, array $additionalArguments = []): array
    {
        $distances = [];
        $embeddings = $this->readDocumentsFromFile();

        $accessor = new PropertyAccessor();

        foreach ($embeddings as $index => $embedding) {
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
            $embedding = clone $embeddings[$index];
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

    /**
     * @return EmbeddingInterface[]
     */
    private function readDocumentsFromFile(): array
    {
        if (!\file_exists($this->filePath)) {
            return [];
        }

        /** @var EmbeddingInterface[] $result */
        $result = \unserialize((string) \file_get_contents($this->filePath));

        return $result;
    }

    /**
     * @param EmbeddingInterface[] $embeddings
     */
    private function saveDocumentsToFile(array $embeddings): void
    {
        \file_put_contents($this->filePath, \serialize($embeddings));
    }

    public function removeDocument(string $identifier): void
    {
        $embeddings = $this->readDocumentsFromFile();

        foreach ($embeddings as $index => $embedding) {
            if ($embedding->getIdentifier() === $identifier) {
                unset($embeddings[$index]);
                $this->saveDocumentsToFile(\array_values($embeddings));

                return;
            }
        }
    }

    public function removeDocuments(array $identifiers): void
    {
        if ([] === $identifiers) {
            return;
        }

        $embeddings = $this->readDocumentsFromFile();
        $identifierSet = \array_flip($identifiers);
        $modified = false;

        foreach ($embeddings as $index => $embedding) {
            if (isset($identifierSet[$embedding->getIdentifier()])) {
                unset($embeddings[$index]);
                $modified = true;
            }
        }

        if ($modified) {
            $this->saveDocumentsToFile(\array_values($embeddings));
        }
    }
}
