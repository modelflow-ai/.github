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

namespace ModelflowAi\Anthropic;

use ModelflowAi\Anthropic\Resources\EmbeddingsInterface;
use ModelflowAi\Anthropic\Resources\MessagesInterface;

interface ClientInterface
{
    /**
     * Send a structured list of input messages with text and/or image content, and the model will generate the next message in the conversation.
     *
     * The Messages API can be used for for either single queries or stateless multi-turn conversations.
     *
     * @see https://docs.anthropic.com/claude/reference/messages_post
     */
    public function messages(): MessagesInterface;

    /**
     * Generate embeddings using Voyage AI (Anthropic's recommended embeddings provider).
     *
     * @see https://docs.anthropic.com/en/docs/build-with-claude/embeddings
     */
    public function embeddings(): EmbeddingsInterface;
}
