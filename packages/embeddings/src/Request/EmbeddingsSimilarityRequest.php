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

use ModelflowAi\Embeddings\Response\EmbeddingsSimilarityResponse;

final class EmbeddingsSimilarityRequest
{
    /**
     * @param callable $execute Function that takes (self $request) and returns EmbeddingsSimilarityResponse
     * @param array<string, scalar> $additionalFilter
     */
    public function __construct(
        private $execute,
        private readonly string $content,
        private readonly string $key,
        private readonly int $limit = 1,
        private readonly array $additionalFilter = [],
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return array<string, scalar>
     */
    public function getAdditionalFilter(): array
    {
        return $this->additionalFilter;
    }

    public function withLimit(int $limit): self
    {
        return new self(
            $this->execute,
            $this->content,
            $this->key,
            $limit,
            $this->additionalFilter,
        );
    }

    /**
     * @param array<string, scalar> $additionalFilter
     */
    public function withAdditionalFilter(array $additionalFilter): self
    {
        return new self(
            $this->execute,
            $this->content,
            $this->key,
            $this->limit,
            $additionalFilter,
        );
    }

    public function execute(): EmbeddingsSimilarityResponse
    {
        $response = ($this->execute)($this);

        if (!$response instanceof EmbeddingsSimilarityResponse) {
            throw new \RuntimeException(
                \sprintf(
                    'The execute callable must return an instance of %s, got %s.',
                    EmbeddingsSimilarityResponse::class,
                    \get_debug_type($response),
                ),
            );
        }

        return $response;
    }
}
