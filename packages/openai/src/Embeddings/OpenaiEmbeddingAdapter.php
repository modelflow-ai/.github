<?php

namespace ModelflowAi\Openai\Embeddings;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use OpenAI\Client;

class OpenaiEmbeddingAdapter implements EmbeddingAdapterInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $model = 'text-embedding-ada-002',
    ) {
    }

    public function embedText(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
            'encoding_format' => 'float',
        ]);

        return $response->embeddings;
    }
}
