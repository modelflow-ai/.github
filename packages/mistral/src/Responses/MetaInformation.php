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

final readonly class MetaInformation
{
    /**
     * @param array<string, string> $headers
     */
    private function __construct(
        public array $headers,
    ) {
    }

    /**
     * @param array<string, string> $headers
     */
    public static function from(array $headers): self
    {
        return new self(
            $headers,
        );
    }
}
