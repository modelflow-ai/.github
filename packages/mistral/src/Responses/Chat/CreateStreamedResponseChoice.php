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

final readonly class CreateStreamedResponseChoice
{
    private function __construct(
        public int $index,
        public CreateStreamedResponseDelta $delta,
        public ?string $finishReason,
    ) {
    }

    /**
     * @param array{
     *     index: int,
     *     delta: array{
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
            CreateStreamedResponseDelta::from($attributes['delta']),
            $attributes['finish_reason'] ?? null,
        );
    }
}
