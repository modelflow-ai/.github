<?php

namespace ModelflowAi\OpenaiAdapter;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Factory\ChatAdapterFactoryInterface;
use ModelflowAi\Core\Factory\EmbeddingAdapterFactoryInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;
use ModelflowAi\OpenaiAdapter\Model\OpenaiModelChatAdapter;
use OpenAI\Client;

final readonly class OpenaiAdapterFactory implements ChatAdapterFactoryInterface, EmbeddingAdapterFactoryInterface
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function createChatAdapter(array $options): AIModelAdapterInterface
    {
        return new OpenaiModelChatAdapter(
            $this->client,
            $options['model'],
        );
    }

    public function createEmbeddingAdapter(array $options): EmbeddingAdapterInterface
    {
        return new OpenaiEmbeddingAdapter(
            $this->client,
            $options['model'],
        );
    }
}
