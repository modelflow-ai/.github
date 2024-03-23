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

final readonly class CreateStreamedResponseToolCallFunction
{
    private function __construct(
        public string $name,
        public string $arguments,
    ) {
    }

    /**
     * @param  array{
     *     name: string,
     *     arguments: string,
     * } $attributes
     */
    public static function from(array $attributes): self
    {
        return new self(
            $attributes['name'],
            $attributes['arguments'],
        );
    }
}
