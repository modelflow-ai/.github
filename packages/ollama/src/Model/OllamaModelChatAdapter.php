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

namespace ModelflowAi\Ollama\Model;

use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIChatMessage;
use ModelflowAi\Core\Request\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

class OllamaModelChatAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $model = 'llama2',
        private readonly string $url = 'http://localhost:11434/api',
    ) {
        \ini_set('default_socket_timeout', -1);
    }

    /**
     * @param AIChatRequest $request
     */
    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AIChatRequest::class);

        $response = $this->client->request('POST', $this->url . '/chat', [
            'json' => [
                'model' => $this->model,
                'messages' => $request->getMessages()->toArray(),
                'stream' => false,
            ],
        ]);

        $content = \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return new AIChatResponse(
            $request,
            new AIChatMessage(
                AIChatMessageRoleEnum::from($content['message']['role']),
                $content['message']['content'],
            ),
        );
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
