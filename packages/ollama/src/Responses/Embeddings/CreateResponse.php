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

namespace ModelflowAi\Ollama\Responses\Embeddings;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Responses\Usage;

final readonly class CreateResponse
{
    /**
     * @param float[] $embedding
     */
    private function __construct(
        public array $embedding,
        public Usage $usage,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array{
     *     embedding: float[],
     * } $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        return new self(
            $attributes['embedding'],
            Usage::from([
                'prompt_tokens' => 0,
                'completion_tokens' => null,
                'total_tokens' => 0,
            ]),
            $meta,
        );
    }
}
