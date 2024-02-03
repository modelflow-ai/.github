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
            Model::from(Model::EMBED->value),
        );
    }
}
