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

use ModelflowAi\Ollama\Resources\ChatInterface;
use ModelflowAi\Ollama\Resources\CompletionInterface;
use ModelflowAi\Ollama\Resources\EmbeddingsInterface;

interface ClientInterface
{
    /**
     * Given a chat conversation, the model will return a chat completion response.
     *
     * @see https://github.com/jmorganca/ollama/blob/main/docs/api.md#generate-a-chat-completion
     */
    public function chat(): ChatInterface;

    /**
     * Given a prompt, the model will return a text completion response.
     *
     * @see https://github.com/jmorganca/ollama/blob/main/docs/api.md#generate-a-completion
     */
    public function completion(): CompletionInterface;

    /**
     * Get a vector representation of a given input that can be easily consumed by machine learning models and algorithms.
     *
     * @see https://github.com/jmorganca/ollama/blob/main/docs/api.md#generate-embeddings
     */
    public function embeddings(): EmbeddingsInterface;
}
