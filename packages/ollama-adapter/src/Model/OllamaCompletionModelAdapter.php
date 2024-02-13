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
use ModelflowAi\Core\Request\AIRequestInterface;
use ModelflowAi\Core\Request\AICompletionRequest;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Core\Response\AICompletionResponse;
use ModelflowAi\Ollama\ClientInterface;
use Webmozart\Assert\Assert;

final readonly class OllamaCompletionModelAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private ClientInterface $client,
        private string $model = 'llama2',
    ) {
    }

    /**
     * @param AICompletionRequest $request
     */
    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AICompletionRequest::class);

        $response = $this->client->completion()->create([
            'model' => $this->model,
            'prompt' => $request->getPrompt(),
        ]);

        return new AICompletionResponse($request, $response->response);
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AICompletionRequest;
    }
}
