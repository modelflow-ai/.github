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

namespace ModelflowAi\Mistral\Resources;

use ModelflowAi\Mistral\Responses\Chat\CreateResponse;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;

interface ChatInterface
{
    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "system"|"user"|"assistant"|"tool",
     *         content: string,
     *     }>,
     *     temperature?: float,
     *     top_p?: float,
     *     max_tokens?: int,
     *     safe_mode?: boolean,
     *     random_seed?: int,
     *     response_format?: array{ type: "json_object" },
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "system"|"user"|"assistant"|"tool",
     *         content: string,
     *     }>,
     *     temperature?: float,
     *     top_p?: float,
     *     max_tokens?: int,
     *     safe_mode?: boolean,
     *     random_seed?: int,
     *     response_format?: array{ type: "json_object" },
     * } $parameters
     *
     * @return \Iterator<int, CreateStreamedResponse>
     */
    public function createStreamed(array $parameters): \Iterator;
}
