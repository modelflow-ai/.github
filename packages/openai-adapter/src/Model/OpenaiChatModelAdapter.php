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
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Core\Response\AIChatToolCall;
use ModelflowAi\Core\Response\AIChatToolResponse;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Core\Tool\ToolTypeEnum;
use ModelflowAi\OpenaiAdapter\Tool\ToolFormatter;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\StreamResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use Webmozart\Assert\Assert;

final readonly class OpenaiChatModelAdapter implements AIModelAdapterInterface
{
    public function __construct(
        private ClientContract $client,
        private string $model = 'gpt-4',
    ) {
    }

    public function handleRequest(AIRequestInterface $request): AIResponseInterface
    {
        Assert::isInstanceOf($request, AIChatRequest::class);

        /** @var string|null $format */
        $format = $request->getOption('format');
        Assert::inArray($format, [null, 'json'], \sprintf('Invalid format "%s" given.', $format));

        $parameters = [
            'model' => $this->model,
            'messages' => $request->getMessages()->toArray(),
        ];

        if ('json' === $format) {
            $parameters['response_format'] = ['type' => 'json_object'];
        }


        if ($request->getOption('streamed', false)) {
            return $this->createStreamed($request, $parameters);
        }

        return $this->create($request, $parameters);
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{role: "assistant"|"system"|"user", content: string}>,
     *     response_format?: array{type: "json_object"},
     * } $parameters
     */
    protected function create(AIChatRequest $request, array $parameters): AIResponseInterface
    {
        $parameters['tools'] = ToolFormatter::formatTools($request->getToolInfos());
        $parameters['tool_choice'] = $request->getOption('toolChoice');

        $result = $this->client->chat()->create($parameters);

        $choice = $result->choices[0];
        if (0 < \count($choice->message->toolCalls)) {
            return new AIChatToolResponse(
                $request,
                \array_map(
                    fn(CreateResponseToolCall $toolCall) => new AIChatToolCall(
                        ToolTypeEnum::from($toolCall->type),
                        $toolCall->id,
                        $toolCall->function->name,
                        json_decode($toolCall->function->arguments, true),
                    ),
                    $choice->message->toolCalls,
                )
            );
        }

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($choice->message->role),
                $choice->message->content ?? '',
            ),
        );
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{role: "assistant"|"system"|"user", content: string}>,
     *     response_format?: array{type: "json_object"},
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
     * @param StreamResponse<CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    protected function createStreamedMessages(StreamResponse $responses): \Iterator
    {
        $role = null;

        /** @var CreateStreamedResponse $response */
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
