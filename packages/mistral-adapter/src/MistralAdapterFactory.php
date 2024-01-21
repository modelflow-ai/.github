<?php

namespace ModelflowAi\MistralAdapter;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Factory\ChatAdapterFactoryInterface;
use ModelflowAi\Core\Factory\EmbeddingAdapterFactoryInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\MistralAdapter\Embeddings\MistralEmbeddingAdapter;
use ModelflowAi\MistralAdapter\Model\MistralChatModelAdapter;

final readonly class MistralAdapterFactory implements ChatAdapterFactoryInterface, EmbeddingAdapterFactoryInterface
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function createChatAdapter(array $options): AIModelAdapterInterface
    {
        return new MistralChatModelAdapter(
            $this->client,
            Model::from($options['model']),
        );
    }

    public function createEmbeddingAdapter(array $options): EmbeddingAdapterInterface
    {
        return new MistralEmbeddingAdapter(
            $this->client,
            Model::from($options['model'] ?? Model::EMBED->value),
        );
    }
}
