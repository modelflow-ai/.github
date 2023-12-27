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
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Request\AITextRequest;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Core\Response\AITextResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

class OllamaModelTextAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $model = 'llama2',
        private readonly string $url = 'http://localhost:11434/api',
    ) {
        \ini_set('default_socket_timeout', -1);
    }

    /**
     * @param AITextRequest $request
     */
    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AITextRequest::class);

        $response = $this->client->request('POST', $this->url . '/generate', [
            'json' => [
                'model' => $this->model,
                'prompt' => $request->getText(),
                'stream' => false,
            ],
        ]);

        $content = \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        return new AITextResponse($request, $content['response']);
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AITextRequest;
    }
}
