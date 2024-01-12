<?php

namespace ModelflowAi\Mistral\Resources;

use ModelflowAi\Mistral\Responses\Chat\CreateResponse;

interface ChatInterface
{
    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "system"|"user"|"assistant",
     *         content: string,
     *     }>,
     *     temperature?: float,
     *     top_p?: float,
     *     max_tokens?: int,
     *     safe_mode?: boolean,
     *     random_seed?: int,
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;
}
