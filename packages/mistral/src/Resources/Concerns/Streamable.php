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

namespace ModelflowAi\Mistral\Resources\Concerns;

trait Streamable
{
    /**
     * @param array<string, mixed> $parameters
     */
    private function ensureNotStreamed(array $parameters): void
    {
        if (!isset($parameters['stream'])) {
            return;
        }

        if (true !== $parameters['stream']) {
            return;
        }

        throw new \InvalidArgumentException('Stream option is not supported. Please use the createStreamed() method instead.');
    }

    /**
     * Set the stream parameter to true.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function setStreamParameter(array $parameters): array
    {
        $parameters['stream'] = true;

        return $parameters;
    }
}
