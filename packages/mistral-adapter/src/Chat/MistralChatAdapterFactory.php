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

namespace ModelflowAi\MistralAdapter\Chat;

use ModelflowAi\Chat\Adapter\AIChatAdapterFactoryInterface;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Mistral\ClientInterface;

final readonly class MistralChatAdapterFactory implements AIChatAdapterFactoryInterface
{
    public function __construct(
        private ClientInterface $client,
        private ?ReasoningEffortEnum $reasoningEffort = null,
    ) {
    }

    public function createChatAdapter(array $options): AIChatAdapterInterface
    {
        return new MistralChatAdapter(
            $this->client,
            $options['model'],
            $this->reasoningEffort,
        );
    }
}
