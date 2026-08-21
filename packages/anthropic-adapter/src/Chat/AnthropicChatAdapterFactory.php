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

namespace ModelflowAi\AnthropicAdapter\Chat;

use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\Chat\Adapter\AIChatAdapterFactoryInterface;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;

final readonly class AnthropicChatAdapterFactory implements AIChatAdapterFactoryInterface
{
    public function __construct(
        private ClientInterface $client,
        private int $maxTokens = 1024,
        private ?ThinkingModeEnum $thinking = null,
    ) {
    }

    public function createChatAdapter(array $options): AIChatAdapterInterface
    {
        return new AnthropicChatAdapter(
            $this->client,
            $options['model'],
            $this->maxTokens,
            $this->thinking,
        );
    }
}
