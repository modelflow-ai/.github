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
