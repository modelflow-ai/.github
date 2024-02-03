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

namespace ModelflowAi\OpenaiAdapter;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Factory\ChatAdapterFactoryInterface;
use ModelflowAi\Core\Factory\EmbeddingAdapterFactoryInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\OpenaiAdapter\Embeddings\OpenaiEmbeddingAdapter;
use ModelflowAi\OpenaiAdapter\Model\OpenaiChatModelAdapter;
use OpenAI\Contracts\ClientContract;

final readonly class OpenaiAdapterFactory implements ChatAdapterFactoryInterface, EmbeddingAdapterFactoryInterface
{
    public function __construct(
        private ClientContract $client,
    ) {
    }

    public function createChatAdapter(array $options): AIModelAdapterInterface
    {
        return new OpenaiChatModelAdapter(
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
