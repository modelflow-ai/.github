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

namespace ModelflowAi\GoogleGeminiAdapter\Chat;

use Gemini\Contracts\ClientContract;
use Gemini\Enums\ThinkingLevel;
use ModelflowAi\Chat\Adapter\AIChatAdapterFactoryInterface;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;

final readonly class GoogleGeminiChatAdapterFactory implements AIChatAdapterFactoryInterface
{
    public function __construct(
        private ClientContract $client,
        private ?ThinkingLevel $thinkingLevel = null,
    ) {
    }

    public function createChatAdapter(array $options): AIChatAdapterInterface
    {
        return new GoogleGeminiChatAdapter(
            $this->client,
            $options['model'],
            $this->thinkingLevel,
        );
    }
}
