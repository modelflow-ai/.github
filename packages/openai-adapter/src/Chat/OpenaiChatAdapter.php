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

namespace ModelflowAi\OpenaiAdapter\Chat;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ToolCallPart;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Request\ResponseFormat\SupportsResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\StreamResponse;
use Webmozart\Assert\Assert;

final readonly class OpenaiChatAdapter implements AIChatAdapterInterface, SupportsResponseFormatInterface
{
    private const RESPONSE_TYPE_JSON_OBJECT = 'json_object';
    private const RESPONSE_TYPE_JSON_SCHEMA = 'json_schema';

    public function __construct(
        private ClientContract $client,
        private string $model = 'gpt-4',
    ) {
    }

    public function handleRequest(AIChatRequest $request): AIChatResponse
    {
        /** @var string|null $format */
        $format = $request->getFormat();
        Assert::inArray($format, [null, 'json', 'json_schema'], \sprintf('Invalid format "%s" given.', $format));

        $parameters = [
            'model' => $this->model,
        ];
        if (null !== $format) {
            $schema = $request->getResponseFormat();
            if (!$schema instanceof JsonSchemaResponseFormat) {
                $parameters['response_format'] = ['type' => self::RESPONSE_TYPE_JSON_OBJECT];
            } else {
                $parameters['response_format'] = [
                    'type' => self::RESPONSE_TYPE_JSON_SCHEMA,
                    'json_schema' => [
                        'name' => 'response',
                        'strict' => true,
                        'schema' => $schema->schema,
                    ],
                ];
            }
        }

        $messages = [];

        /** @var AIChatMessage $aiMessage */
        foreach ($request->getMessages() as $aiMessage) {
            $message = [
                'role' => $aiMessage->role->value,
                'content' => [],
            ];

            foreach ($aiMessage->parts as $part) {
                if ($part instanceof TextPart) {
                    $message['content'][] = [
                        'type' => 'text',
                        'text' => $part->text,
                    ];
                } elseif ($part instanceof ImageBase64Part) {
                    $message['content'][] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => \sprintf('data:%s;base64,%s', $part->mimeType, $part->content),
                        ],
                    ];
                } elseif ($part instanceof ToolCallsPart) {
                    $message['tool_calls'] = \array_map(
                        static fn (AIChatToolCall $tool) => [
                            'id' => $tool->id,
                            'type' => $tool->type->value,
                            'function' => [
                                'name' => $tool->name,
                                'arguments' => (string) \json_encode($tool->arguments),
                            ],
                        ],
                        $part->toolCalls,
                    );
                } elseif ($part instanceof ToolCallPart) {
                    $message['tool_call_id'] = $part->toolCallId;
                    $message['name'] = $part->toolName;
                    $message['content'][] = $part->content;
                } else {
                    throw new \InvalidArgumentException(\sprintf(
                        'Unsupported message part type: %s. Supported types are: %s',
                        $part::class,
                        \implode(', ', [
                            TextPart::class,
                            ImageBase64Part::class,
                            ToolCallsPart::class,
                            ToolCallPart::class,
                        ]),
                    ));
                }
            }

            $message['content'] = $this->normalizeMessageContent($message['content']);

            $messages[] = $message;
        }

        $parameters['messages'] = $messages;

        if ($seed = $request->getOption('seed')) {
            /** @var int $seed */
            $parameters['seed'] = $seed;
        }

        if ($temperature = $request->getOption('temperature')) {
            /** @var float $temperature */
            $parameters['temperature'] = $temperature;
        }

        if ($request->hasTools()) {
            $parameters['tools'] = ToolFormatter::formatTools($request->getToolInfos());
            $toolChoice = $request->getToolChoice();
            $parameters['tool_choice'] = $toolChoice->value;
        }

        if ($request instanceof AIChatStreamedRequest) {
            return $this->createStreamed($request, $parameters);
        }

        return $this->create($request, $parameters);
    }

    /**
     * @param array<string|array{
     *     type: "text",
     *     text: string,
     * }|array{
     *     type: "image_url",
     *     image_url: array{
     *         url: string,
     *     },
     * }> $content
     *
     * @return string|array<string|array{
     *     type: "text",
     *     text: string,
     * }|array{
     *     type: "image_url",
     *     image_url: array{
     *         url: string,
     *     },
     * }>
     */
    private function normalizeMessageContent(array $content): string|array
    {
        if (1 !== \count($content)) {
            return $content;
        }

        if (\is_string($content[0])) {
            return $content[0];
        }

        if ('text' === $content[0]['type']) {
            return $content[0]['text'];
        }

        return $content;
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "assistant"|"system"|"user"|"tool",
     *         content: string|array<string|array{
     *             type: "text",
     *             text: string,
     *         }|array{
     *             type: "image_url",
     *             image_url: array{
     *                 url: string,
     *             },
     *         }>,
     *         tool_calls?: array<array{
     *             id: string,
     *             type: string,
     *             function: array{
     *                 name: string,
     *                 arguments: string,
     *             },
     *         }>,
     *     }>,
     *     response_format?: array{
     *         type: "json_object",
     *     }|array{
     *         type: "json_schema",
     *         json_schema?: array{
     *             name: string,
     *             strict: bool,
     *             schema: array<string, mixed>,
     *         },
     *     },
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description: string,
     *             parameters: array{
     *                 type: string,
     *                 properties: array<string, mixed[]>|\stdClass,
     *                 required: string[],
     *            },
     *         },
     *     }>,
     *     tool_choice?: string,
     *     seed?: int,
     *     temperature?: float,
     * } $parameters
     */
    private function create(AIChatRequest $request, array $parameters): AIChatResponse
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
                new Usage(
                    $result->usage->promptTokens,
                    $result->usage->completionTokens ?? 0,
                    $result->usage->totalTokens,
                ),
            );
        }

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($choice->message->role),
                $choice->message->content ?? '',
            ),
            new Usage(
                $result->usage->promptTokens,
                $result->usage->completionTokens ?? 0,
                $result->usage->totalTokens,
            ),
        );
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "assistant"|"system"|"user"|"tool",
     *         content: string|array<string|array{
     *             type: "text",
     *             text: string,
     *         }|array{
     *             type: "image_url",
     *             image_url: array{
     *                 url: string,
     *             },
     *         }>,
     *         tool_calls?: array<array{
     *             id: string,
     *             type: string,
     *             function: array{
     *                 name: string,
     *                 arguments: string,
     *             },
     *         }>,
     *     }>,
     *     response_format?: array{
     *         type: "json_object",
     *     }|array{
     *         type: "json_schema",
     *         json_schema?: array{
     *             name: string,
     *             strict: bool,
     *             schema: array<string, mixed>,
     *         },
     *     },
     *     tools?: array<array{
     *         type: string,
     *         function: array{
     *             name: string,
     *             description: string,
     *             parameters: array{
     *                 type: string,
     *                 properties: array<string, mixed[]>|\stdClass,
     *                 required: string[],
     *            },
     *         },
     *     }>,
     *      tool_choice?: string,
     *      seed?: int,
     *      temperature?: float,
     * } $parameters
     */
    private function createStreamed(AIChatStreamedRequest $request, array $parameters): AIChatResponse
    {
        $parameters['stream_options'] = ['include_usage' => true];

        $responses = $this->client->chat()->createStreamed($parameters);

        $usageTracker = new StreamingUsageTracker(false);

        return new AIChatResponseStream(
            request: $request,
            messages: $this->createStreamedMessages($responses, $usageTracker),
            usageTracker: $usageTracker,
        );
    }

    /**
     * @param StreamResponse<CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    private function createStreamedMessages(StreamResponse $responses, ?StreamingUsageTracker $usageTracker = null): \Iterator
    {
        $role = null;

        /** @var CreateStreamedResponse $response */
        foreach ($responses as $response) {
            if ($usageTracker instanceof StreamingUsageTracker && null !== $response->usage) {
                $usage = new Usage(
                    $response->usage->promptTokens,
                    $response->usage->completionTokens ?? 0,
                    $response->usage->totalTokens,
                );
                $usageTracker->updateUsage($usage, true);
            }

            if (0 === \count($response->choices)) {
                continue;
            }

            $delta = $response->choices[0]->delta;

            if (!$role instanceof AIChatMessageRoleEnum) {
                $role = AIChatMessageRoleEnum::from($delta->role ?? 'assistant');
            }

            if (0 < \count($delta->toolCalls)) {
                foreach ($this->determineToolCall($responses, $response, $usageTracker) as $toolCall) {
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
    private function determineToolCall(StreamResponse $responses, CreateStreamedResponse $firstResponse, ?StreamingUsageTracker $usageTracker = null): \Iterator
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
            if ($usageTracker instanceof StreamingUsageTracker && null !== $response->usage) {
                $usage = new Usage(
                    $response->usage->promptTokens,
                    $response->usage->completionTokens ?? 0,
                    $response->usage->totalTokens,
                );
                $usageTracker->updateUsage($usage, true);
            }

            if (0 === \count($response->choices)) {
                continue;
            }

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
    private function decodeArguments(string $arguments): array
    {
        /** @var array<string, mixed> $result */
        $result = \json_decode($arguments, true);

        return $result;
    }

    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest;
    }

    public function supportsResponseFormat(ResponseFormatInterface $responseFormat): bool
    {
        return $responseFormat instanceof JsonSchemaResponseFormat;
    }
}
