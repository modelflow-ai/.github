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

namespace ModelflowAi\Anthropic\Responses\Embeddings;

final readonly class CreateResponse
{
    /**
     * @param EmbeddingData[] $data
     */
    public function __construct(
        public string $object,
        public array $data,
        public string $model,
        public Usage $usage,
    ) {
    }

    public static function from(array $attributes): self
    {
        return new self(
            $attributes['object'],
            \array_map(fn (array $data): EmbeddingData => EmbeddingData::from($data), $attributes['data']),
            $attributes['model'],
            Usage::from($attributes['usage']),
        );
    }
}
