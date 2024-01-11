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

namespace ModelflowAi\Mistral\Transport\ValueObjects;

readonly class ResourceUri implements \Stringable
{
    public function __construct(
        public string $uri,
    ) {
    }

    public static function get(string $resource): self
    {
        return new self($resource);
    }

    public function __toString(): string
    {
        return $this->uri;
    }
}
