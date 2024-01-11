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

final readonly class Usage
{
    private function __construct(
        public int $promptTokens,
        public ?int $completionTokens,
        public int $totalTokens,
    ) {
    }

    /**
     * @param array{
     *     prompt_tokens: int,
     *     completion_tokens: int|null,
     *     total_tokens: int,
     * } $attributes
     */
    public static function from(array $attributes): self
    {
        return new self(
            $attributes['prompt_tokens'],
            $attributes['completion_tokens'] ?? null,
            $attributes['total_tokens'],
        );
    }
}
