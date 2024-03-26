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

final readonly class CreateResponseMessage
{
    /**
     * @param CreateResponseToolCall[] $toolCalls
     */
    private function __construct(
        public string $role,
        public ?string $content,
        public array $toolCalls,
    ) {
    }

    /**
     * @param array{
     *     role: string,
     *     content: ?string,
     *     tool_calls?: array<array{
     *         id: string,
     *         type: string,
     *         function: array{
     *             name: string,
     *             arguments: string,
     *         },
     *     }>
     * } $attributes
     */
    public static function from(array $attributes): self
    {
        $toolCalls = \array_map(fn (array $result, int $index): CreateResponseToolCall => CreateResponseToolCall::from(
            $index,
            $result,
        ), $attributes['tool_calls'] ?? [], \array_keys($attributes['tool_calls'] ?? []));

        return new self(
            $attributes['role'],
            $attributes['content'] ?? null,
            $toolCalls,
        );
    }
}
