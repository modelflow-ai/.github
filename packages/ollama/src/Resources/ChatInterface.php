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

namespace ModelflowAi\Ollama\Resources;

use ModelflowAi\Ollama\Responses\Chat\CreateResponse;
use ModelflowAi\Ollama\Responses\Chat\CreateStreamedResponse;

interface ChatInterface
{
    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "system"|"user"|"assistant",
     *         content: string,
     *         files?: string[],
     *     }>,
     *     format?: "json",
     *     options?: array<string, string|int|float>,
     *     template?: string,
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "system"|"user"|"assistant",
     *         content: string,
     *         files?: string[],
     *     }>,
     *     format?: "json",
     *     options?: array<string, string|int|float>,
     *     template?: string,
     * } $parameters
     *
     * @return \Iterator<int, CreateStreamedResponse>
     */
    public function createStreamed(array $parameters): \Iterator;
}
