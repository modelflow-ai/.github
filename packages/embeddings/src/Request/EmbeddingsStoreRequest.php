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

namespace ModelflowAi\Embeddings\Request;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Response\EmbeddingsStoreResponse;

final class EmbeddingsStoreRequest
{
    /**
     * @param callable $execute Function that takes (EmbeddingInterface[] $embeddings) and returns EmbeddingsStoreResponse
     * @param EmbeddingInterface[] $embeddings
     * @param callable|null $headerGenerator
     */
    public function __construct(
        private $execute,
        private readonly array $embeddings,
        private $headerGenerator = null,
    ) {
    }

    /**
     * @return EmbeddingInterface[]
     */
    public function getEmbeddings(): array
    {
        return $this->embeddings;
    }

    public function getHeaderGenerator(): ?callable
    {
        return $this->headerGenerator;
    }

    public function withHeaderGenerator(?callable $headerGenerator): self
    {
        return new self(
            $this->execute,
            $this->embeddings,
            $headerGenerator,
        );
    }

    public function execute(): EmbeddingsStoreResponse
    {
        $response = ($this->execute)($this);

        if (!$response instanceof EmbeddingsStoreResponse) {
            throw new \RuntimeException(
                \sprintf(
                    'The execute callable must return an instance of %s, got %s.',
                    EmbeddingsStoreResponse::class,
                    \get_debug_type($response),
                ),
            );
        }

        return $response;
    }
}
