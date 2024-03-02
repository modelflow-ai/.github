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
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Responses\Chat\CreateStreamedResponse;
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

        /** @var "json"|null $format */
        $format = $request->getOption('format');
        Assert::inArray($format, [null, 'json'], \sprintf('Invalid format "%s" given.', $format));

        $attributes = [
            'model' => $this->model,
            'messages' => $request->getMessages()->toArray(),
        ];

        if ($format) {
            $attributes['format'] = $format;
        }

        if ($request->getOption('streamed', false)) {
            return $this->createStreamed($request, $attributes);
        }

        return $this->create($request, $attributes);
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{role: "assistant"|"system"|"user", content: string}>,
     *     format?: "json",
     * } $attributes
     */
    protected function create(AIChatRequest $request, array $attributes): AIResponseInterface
    {
        $response = $this->client->chat()->create($attributes);

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($response->message->role),
                $response->message->content ?? '',
            ),
        );
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{role: "assistant"|"system"|"user", content: string}>,
     *     format?: "json",
     * } $attributes
     */
    protected function createStreamed(AIChatRequest $request, array $attributes): AIResponseInterface
    {
        $responses = $this->client->chat()->createStreamed($attributes);

        return new AIChatResponseStream(
            $request,
            $this->createStreamedMessages($responses),
        );
    }

    /**
     * @param \Iterator<int, CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    protected function createStreamedMessages(\Iterator $responses): \Iterator
    {
        $role = null;

        foreach ($responses as $response) {
            if (!$role instanceof AIChatMessageRoleEnum) {
                $role = AIChatMessageRoleEnum::from($response->message->role);
            }

            yield new AIChatResponseMessage(
                $role,
                $response->message->delta ?? '',
            );
        }
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
