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

namespace ModelflowAi\Mistral\Responses;

final readonly class CreateResponseChoice
{
    private function __construct(
        public int $index,
        public CreateResponseMessage $message,
        public ?string $finishReason,
    ) {
    }

    /**
     * @param array{
     *     index: int,
     *     message: array{
     *         role: string,
     *         content: ?string,
     *     },
     *     finish_reason: string|null,
     * } $attributes
     */
    public static function from(array $attributes): self
    {
        return new self(
            $attributes['index'],
            CreateResponseMessage::from($attributes['message']),
            $attributes['finish_reason'] ?? null,
        );
    }
}
