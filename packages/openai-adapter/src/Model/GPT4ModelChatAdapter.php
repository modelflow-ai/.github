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

namespace ModelflowAi\OpenaiAdapter\Model;

use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use OpenAI\Client;
use Webmozart\Assert\Assert;

final readonly class GPT4ModelChatAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private Client $client,
        private string $model = 'gpt-4',
    ) {
    }

    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AIChatRequest::class);

        $result = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $request->getMessages()->toArray(),
        ]);

        return new AIChatResponse(
            $request,
            new AIChatMessage(
                AIChatMessageRoleEnum::from($result->choices[0]->message->role),
                $result->choices[0]->message->content ?? '',
            ),
        );
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
