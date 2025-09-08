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
     *         content: string|array<string|array{type: "text", text: string}|array{type: "image_url", image_url: string}>,
     *     }>,
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description?: string,
     *             parameters?: array<string, mixed>,
     *         },
     *     }>,
     *     tools_choice?: "auto"|"enum",
     *     temperature?: float,
     *     top_p?: float,
     *     max_tokens?: int,
     *     safe_mode?: bool,
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
     *         content: string|array<string|array{type: "text", text: string}|array{type: "image_url", image_url: string}>,
     *     }>,
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description?: string,
     *             parameters?: array<string, mixed>,
     *         },
     *     }>,
     *     tools_choice?: "auto"|"enum",
     *     temperature?: float,
     *     top_p?: float,
     *     max_tokens?: int,
     *     safe_mode?: bool,
     *     random_seed?: int,
     *     response_format?: array{ type: "json_object" },
     * } $parameters
     *
     * @return \Iterator<int, CreateStreamedResponse>
     */
    public function createStreamed(array $parameters): \Iterator;
}
