<?php

namespace ModelflowAi\Mistral;

use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Resources\EmbeddingsInterface;

interface ClientInterface
{
    /**
     * Given a chat conversation, the model will return a chat completion response.
     *
     * @see https://docs.mistral.ai/api/#operation/createChatCompletion
     */
    public function chat(): ChatInterface;

    /**
     * Get a vector representation of a given input that can be easily consumed by machine learning models and algorithms.
     *
     * @see https://docs.mistral.ai/api/#operation/createEmbedding
     */
    public function embeddings(): EmbeddingsInterface;
}
