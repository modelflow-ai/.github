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
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Core\ToolInfo\ToolTypeEnum;
use ModelflowAi\OpenaiAdapter\Tool\ToolFormatter;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\StreamResponse;
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

        if ($request->hasTools()) {
            $parameters['tools'] = ToolFormatter::formatTools($request->getToolInfos());
            $toolChoice = $request->getOption('toolChoice');
            if (null !== $toolChoice) {
                Assert::string($toolChoice);
                $parameters['tool_choice'] = $toolChoice;
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
     *     messages: array<array{
     *         role: "assistant"|"system"|"user"|"tool",
     *         content: string,
     *     }>,
     *     response_format?: array{
     *         type: "json_object",
     *     },
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description: string,
     *             parameters: array{
     *                 type: string,
     *                 properties: array<string, mixed[]>,
     *                 required: string[],
     *            },
     *         },
     *     }>,
     *     tool_choice?: string,
     * } $parameters
     */
    protected function create(AIChatRequest $request, array $parameters): AIResponseInterface
    {
        $result = $this->client->chat()->create($parameters);

        $choice = $result->choices[0];
        if (0 < \count($choice->message->toolCalls)) {
            return new AIChatResponse(
                $request,
                new AIChatResponseMessage(
                    AIChatMessageRoleEnum::from($choice->message->role),
                    $choice->message->content ?? '',
                    \array_map(
                        fn (CreateResponseToolCall $toolCall) => new AIChatToolCall(
                            ToolTypeEnum::from($toolCall->type),
                            $toolCall->id,
                            $toolCall->function->name,
                            $this->decodeArguments($toolCall->function->arguments),
                        ),
                        $choice->message->toolCalls,
                    ),
                ),
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
     *     messages: array<array{
     *         role: "assistant"|"system"|"user"|"tool",
     *         content: string,
     *     }>,
     *     response_format?: array{
     *         type: "json_object",
     *     },
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description: string,
     *             parameters: array{
     *                 type: string,
     *                 properties: array<string, mixed[]>,
     *                 required: string[],
     *            },
     *         },
     *     }>,
     *      tool_choice?: string,
     * } $parameters
     */
    protected function createStreamed(AIChatRequest $request, array $parameters): AIResponseInterface
    {
        $responses = $this->client->chat()->createStreamed($parameters);

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
            $delta = $response->choices[0]->delta;

            if (!$role instanceof AIChatMessageRoleEnum) {
                $role = AIChatMessageRoleEnum::from($delta->role ?? 'assistant');
            }

            if (0 < \count($delta->toolCalls)) {
                foreach ($this->determineToolCall($responses, $response) as $toolCall) {
                    yield new AIChatResponseMessage(
                        $role,
                        $delta->content ?? '',
                        [$toolCall],
                    );
                }

                break;
            }

            if (null !== $delta->content) {
                yield new AIChatResponseMessage(
                    $role,
                    $delta->content,
                );
            }
        }
    }

    /**
     * @param StreamResponse<CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatToolCall>
     */
    protected function determineToolCall(StreamResponse $responses, CreateStreamedResponse $firstResponse): \Iterator
    {
        $message = [
            'id' => $firstResponse->choices[0]->delta->toolCalls[0]->id,
            'type' => ToolTypeEnum::tryFrom($firstResponse->choices[0]->delta->toolCalls[0]->type ?? '') ?? ToolTypeEnum::FUNCTION,
            'function' => [
                'name' => $firstResponse->choices[0]->delta->toolCalls[0]->function->name,
                'arguments' => [
                    $firstResponse->choices[0]->delta->toolCalls[0]->function->arguments,
                ],
            ],
        ];

        /** @var CreateStreamedResponse $response */
        foreach ($responses as $response) {
            $delta = $response->choices[0]->delta;

            foreach ($delta->toolCalls as $toolCall) {
                if (null !== $toolCall->id) {
                    Assert::inArray($message['type'], ToolTypeEnum::cases());
                    Assert::notNull($message['id']);
                    Assert::isArray($message['function']);
                    Assert::notNull($message['function']['name']);
                    Assert::notNull($message['function']['arguments']);

                    yield new AIChatToolCall(
                        $message['type'],
                        $message['id'],
                        $message['function']['name'],
                        $this->decodeArguments(\implode('', $message['function']['arguments'])),
                    );

                    $message = [
                        'id' => $toolCall->id,
                        'type' => ToolTypeEnum::tryFrom($toolCall->type ?? '') ?? ToolTypeEnum::FUNCTION,
                        'function' => [
                            'name' => $toolCall->function->name,
                            'arguments' => [],
                        ],
                    ];
                }

                $message['function']['arguments'][] = $toolCall->function->arguments;
            }
        }

        Assert::inArray($message['type'], ToolTypeEnum::cases());
        Assert::notNull($message['id']);
        Assert::isArray($message['function']);
        Assert::notNull($message['function']['name']);
        Assert::notNull($message['function']['arguments']);

        yield new AIChatToolCall(
            $message['type'],
            $message['id'],
            $message['function']['name'],
            $this->decodeArguments(\implode('', $message['function']['arguments'])),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeArguments(string $arguments): array
    {
        /** @var array<string, mixed> $result */
        $result = \json_decode($arguments, true);

        return $result;
    }

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
