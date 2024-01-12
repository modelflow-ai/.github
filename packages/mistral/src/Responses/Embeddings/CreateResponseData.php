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

namespace ModelflowAi\Mistral\Responses\Embeddings;

final readonly class CreateResponseData
{
    /**
     * @param float[] $embedding
     */
    private function __construct(
        public string $object,
        public array $embedding,
        public int $index,
    ) {
    }

    /**
     * @param array{
     *     object: string,
     *     embedding: float[],
     *     index: int,
     * } $attributes
     */
    public static function from(array $attributes): self
    {
        return new self(
            $attributes['object'],
            $attributes['embedding'],
            $attributes['index'],
        );
    }
}
