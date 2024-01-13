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

namespace ModelflowAi\Mistral;

use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Resources\Chat;
use ModelflowAi\Mistral\Resources\ChatInterface;
use ModelflowAi\Mistral\Resources\Embeddings;
use ModelflowAi\Mistral\Resources\EmbeddingsInterface;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    /**
     * Given a chat conversation, the model will return a chat completion response.
     *
     * @see https://docs.mistral.ai/api/#operation/createChatCompletion
     */
    public function chat(): ChatInterface
    {
        return new Chat($this->transport);
    }

    /**
     * Get a vector representation of a given input that can be easily consumed by machine learning models and algorithms.
     *
     * @see https://docs.mistral.ai/api/#operation/createEmbedding
     */
    public function embeddings(): EmbeddingsInterface
    {
        return new Embeddings($this->transport);
    }
}
