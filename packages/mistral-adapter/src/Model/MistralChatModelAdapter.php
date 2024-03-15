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
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
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

        $parameters = [
            'model' => $this->model->value,
            'messages' => $request->getMessages()->toArray(),
        ];

        if ($this->model->jsonSupported()) {
            /** @var string|null $format */
            $format = $request->getOption('format');
            Assert::inArray($format, [null, 'json'], \sprintf('Invalid format "%s" given.', $format));

            if ('json' === $format) {
                $parameters['response_format'] = ['type' => 'json_object'];
            }
        }

        if ($request->getOption('streamed', false)) {
            return $this->createStreamed($request, $parameters);
        }

        return $this->create($request, $parameters);
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{role: "assistant"|"system"|"user"|"tool", content: string}>,
     *     response_format?: array{ type: "json_object" },
     * } $parameters
     */
    protected function create(AIChatRequest $request, array $parameters): AIChatResponse
    {
        $result = $this->client->chat()->create($parameters);

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($result->choices[0]->message->role),
                $result->choices[0]->message->content ?? '',
            ),
        );
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{role: "assistant"|"system"|"user"|"tool", content: string}>,
     *     response_format?: array{ type: "json_object" },
     * } $parameters
     */
    protected function createStreamed(AIChatRequest $request, array $parameters): AIChatResponse
    {
        $responses = $this->client->chat()->createStreamed($parameters);

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
                $role = AIChatMessageRoleEnum::from($response->choices[0]->delta->role ?? 'assistant');
            }

            yield new AIChatResponseMessage(
                $role,
                $response->choices[0]->delta->content ?? '',
            );
        }
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
