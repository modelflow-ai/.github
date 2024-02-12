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

namespace ModelflowAi\MistralAdapter\Model;

use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use Webmozart\Assert\Assert;

final readonly class MistralChatModelAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private ClientInterface $client,
        private Model $model = Model::TINY,
    ) {
    }

    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AIChatRequest::class);

        $result = $this->client->chat()->create([
            'model' => $this->model->value,
            'messages' => $request->getMessages()->toArray(),
        ]);

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
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
