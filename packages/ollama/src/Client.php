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

namespace ModelflowAi\Ollama;

use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Ollama\Resources\Chat;
use ModelflowAi\Ollama\Resources\ChatInterface;
use ModelflowAi\Ollama\Resources\Completion;
use ModelflowAi\Ollama\Resources\CompletionInterface;
use ModelflowAi\Ollama\Resources\Embeddings;
use ModelflowAi\Ollama\Resources\EmbeddingsInterface;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function chat(): ChatInterface
    {
        return new Chat($this->transport);
    }

    public function completion(): CompletionInterface
    {
        return new Completion($this->transport);
    }

    public function embeddings(): EmbeddingsInterface
    {
        return new Embeddings($this->transport);
    }
}
