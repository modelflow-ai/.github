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

final readonly class CreateStreamedResponseToolCall
{
    private function __construct(
        public string $id,
        public string $type,
        public CreateStreamedResponseToolCallFunction $function,
    ) {
    }

    /**
     * @param array{
     *     id?: string,
     *     type?: string,
     *     function: array{
     *         name: string,
     *         arguments: string,
     *     },
     * } $attributes
     */
    public static function from(int $index, array $attributes): self
    {
        return new self(
            $attributes['id'] ?? (string) $index,
            $attributes['type'] ?? 'function',
            CreateStreamedResponseToolCallFunction::from($attributes['function']),
        );
    }
}
