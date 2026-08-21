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

namespace ModelflowAi\OpenaiAdapter\Chat;

use ModelflowAi\Chat\Adapter\AIChatAdapterFactoryInterface;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use OpenAI\Contracts\ClientContract;

final readonly class OpenaiChatAdapterFactory implements AIChatAdapterFactoryInterface
{
    public function __construct(
        private ClientContract $client,
        private ?ReasoningEffortEnum $reasoningEffort = null,
    ) {
    }

    public function createChatAdapter(array $options): AIChatAdapterInterface
    {
        $model = (string) $options['model'];

        // Adapter keys have historically been written without the dash, such as "gpt4o" for the
        // "gpt-4o" model. Newer model ids already carry it and must not get a second one.
        if (\str_starts_with($model, 'gpt') && !\str_starts_with($model, 'gpt-')) {
            $model = 'gpt-' . \substr($model, 3);
        }

        return new OpenaiChatAdapter(
            $this->client,
            $model,
            $this->reasoningEffort,
        );
    }
}
