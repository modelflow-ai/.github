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
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Heavenly inspired by LLPhant
 * https://github.com/theodo-group/LLPhant/blob/main/src/Embeddings/VectorStores/FileSystem/FileSystemVectorStore.php.
 */
class FilesystemEmbeddingsStore implements EmbeddingsStoreInterface
{
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
            $results[] = $embeddings[$index];
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
}
