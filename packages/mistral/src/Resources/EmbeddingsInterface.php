<?php

namespace ModelflowAi\Mistral\Resources;

use ModelflowAi\Mistral\Responses\Embeddings\CreateResponse;

interface EmbeddingsInterface
{
    /**
     * @param array{
     *     model?: string,
     *     input: string[],
     * } $parameters
     */
    public function create(array $parameters): CreateResponse;
}
