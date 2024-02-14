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

namespace ModelflowAi\OllamaAdapter\Model;

use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Ollama\ClientInterface;
use Webmozart\Assert\Assert;

final readonly class OllamaChatModelAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private ClientInterface $client,
        private string $model = 'llama2',
    ) {
    }

    /**
     * @param AIChatRequest $request
     */
    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AIChatRequest::class);

        $format = $request->getOption('format');
        Assert::inArray($format, [null, 'json'], \sprintf('Invalid format "%s" given.', $format));

        $response = $this->client->chat()->create(\array_filter([
            'model' => $this->model,
            'format' => $format,
            'messages' => $request->getMessages()->toArray(),
        ]));

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($response->message->role),
                $response->message->content ?? '',
            ),
        );
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
