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

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Responses\Usage;

final readonly class CreateResponse
{
    /**
     * @param CreateResponseData[] $data
     */
    private function __construct(
        public string $id,
        public string $object,
        public array $data,
        public string $model,
        public Usage $usage,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array{
     *     id: string,
     *     object: string,
     *     data: array<int, array{
     *         object: string,
     *         embedding: float[],
     *         index: int,
     *     }>,
     *     model: string,
     *     usage: array{
     *         prompt_tokens: int,
     *         completion_tokens?: int|null,
     *         total_tokens: int,
     *     }
     * } $attributes
     */
    public static function from(array $attributes, MetaInformation $meta): self
    {
        $data = \array_map(fn (array $result): CreateResponseData => CreateResponseData::from(
            $result,
        ), $attributes['data']);

        return new self(
            $attributes['id'],
            $attributes['object'],
            $data,
            $attributes['model'],
            Usage::from($attributes['usage']),
            $meta,
        );
    }
}
