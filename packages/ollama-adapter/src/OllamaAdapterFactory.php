<?php

namespace ModelflowAi\OllamaAdapter;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Factory\ChatAdapterFactoryInterface;
use ModelflowAi\Core\Factory\EmbeddingAdapterFactoryInterface;
use ModelflowAi\Core\Factory\TextAdapterFactoryInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Ollama\ClientInterface;

use ModelflowAi\OllamaAdapter\Embeddings\OllamaEmbeddingAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaChatModelAdapter;
use ModelflowAi\OllamaAdapter\Model\OllamaTextModelAdapter;

final readonly class OllamaAdapterFactory implements ChatAdapterFactoryInterface, TextAdapterFactoryInterface, EmbeddingAdapterFactoryInterface
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function createChatAdapter(array $options): AIModelAdapterInterface
    {
        return new OllamaChatModelAdapter(
            $this->client,
            $options['model'],
        );
    }

    public function createTextAdapter(array $options): AIModelAdapterInterface
    {
        return new OllamaTextModelAdapter(
            $this->client,
            $options['model'],
        );
    }

    public function createEmbeddingAdapter(array $options): EmbeddingAdapterInterface
    {
        return new OllamaEmbeddingAdapter(
            $this->client,
            $options['model'],
        );
    }
}
