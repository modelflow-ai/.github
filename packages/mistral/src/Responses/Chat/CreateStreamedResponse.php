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

namespace ModelflowAi\Mistral\Responses\Chat;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Responses\Usage;

final readonly class CreateStreamedResponse
{
    /**
     * @param CreateStreamedResponseChoice[] $choices
     */
    private function __construct(
        public string $id,
        public string $object,
        public int $created,
        public string $model,
        public int $index,
        public array $choices,
        public ?Usage $usage,
        public MetaInformation $meta,
    ) {
    }

    /**
     * @param array{
     *     id: string,
     *     object: string,
     *     created: int,
     *     model: string,
     *     choices: array<int, array{
     *         index: int,
     *         delta: array{
     *             role: string,
     *             content: ?string,
     *         },
     *         finish_reason: string|null,
     *     }>,
     *     usage?: array{
     *         prompt_tokens: int,
     *         completion_tokens: int|null,
     *         total_tokens: int,
     *     }|null,
     * } $attributes
     */
    public static function from(int $index, array $attributes, MetaInformation $meta): self
    {
        $choices = \array_map(fn (array $result): CreateStreamedResponseChoice => CreateStreamedResponseChoice::from(
            $result,
        ), $attributes['choices']);

        return new self(
            $attributes['id'],
            $attributes['object'],
            $attributes['created'],
            $attributes['model'],
            $index,
            $choices,
            ($attributes['usage'] ?? null) ? Usage::from($attributes['usage']) : null,
            $meta,
        );
    }
}
