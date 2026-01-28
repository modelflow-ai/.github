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

namespace ModelflowAi\MistralAdapter\Chat;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ToolCallPart;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Chat\CreateResponseToolCall;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
use Webmozart\Assert\Assert;

final readonly class MistralChatAdapter implements AIChatAdapterInterface
{
    public function __construct(
        private ClientInterface $client,
        private string $model = Model::TINY->value,
    ) {
    }

    public function handleRequest(AIChatRequest $request): AIChatResponse
    {
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
                        'image_url' => \sprintf('data:%s;base64,%s', $part->mimeType, $part->content),
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
                    throw new \Exception('Not supported message part type.');
                }
            }

            if (1 === \count($message['content'])) {
                if (\is_string($message['content'][0])) {
                    $message['content'] = $message['content'][0];
                } elseif ('text' === $message['content'][0]['type']) {
                    $message['content'] = $message['content'][0]['text'];
                }
            }

            $messages[] = $message;
        }

        $parameters = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        if (Model::from($this->model)->jsonSupported()) {
            $format = $request->getFormat();
            Assert::inArray($format, [null, 'json', 'json_schema'], \sprintf('Invalid format "%s" given.', $format));

            if ('json' === $format || 'json_schema' === $format) {
                $parameters['response_format'] = ['type' => 'json_object'];
            }
        }

        if ($request->hasTools()) {
            $parameters['tools'] = ToolFormatter::formatTools($request->getToolInfos());
            $toolChoice = $request->getToolChoice();
            $parameters['tool_choice'] = $toolChoice->value;
        }

        if ($seed = $request->getOption('seed')) {
            $parameters['random_seed'] = $seed;
        }

        if ($temperature = $request->getOption('temperature')) {
            $parameters['temperature'] = $temperature;
        }

        if ($request instanceof AIChatStreamedRequest) {
            return $this->createStreamed($request, $parameters);
        }

        return $this->create($request, $parameters);
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "assistant"|"system"|"user"|"tool",
     *         content: string|array<string|array{type: "text", text: string}|array{type: "image_url", image_url: string}>,
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
     *             },
     *         },
     *     }>,
     *     tool_choice?: string,
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
     *         content: string|array<string|array{type: "text", text: string}|array{type: "image_url", image_url: string}>,
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
     *             },
     *         },
     *     }>,
     *     tool_choice?: string,
     * } $parameters
     */
    private function createStreamed(AIChatStreamedRequest $request, array $parameters): AIChatResponse
    {
        $responses = $this->client->chat()->createStreamed($parameters);

        $usageTracker = new StreamingUsageTracker(false);

        return new AIChatResponseStream(
            request: $request,
            messages: $this->createStreamedMessages($responses, $usageTracker),
            usageTracker: $usageTracker,
        );
    }

    /**
     * @param \Iterator<int, CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    private function createStreamedMessages(\Iterator $responses, ?StreamingUsageTracker $usageTracker = null): \Iterator
    {
        $role = null;

        foreach ($responses as $response) {
            if ($usageTracker instanceof StreamingUsageTracker && null !== $response->usage) {
                $usage = new Usage(
                    $response->usage->promptTokens,
                    $response->usage->completionTokens ?? 0,
                    $response->usage->totalTokens,
                );
                $usageTracker->updateUsage($usage, true);
            }

            $delta = $response->choices[0]->delta;

            if (!$role instanceof AIChatMessageRoleEnum) {
                $role = AIChatMessageRoleEnum::from($delta->role ?? 'assistant');
            }

            if (0 < \count($delta->toolCalls)) {
                foreach ($this->determineToolCall($response) as $toolCall) {
                    yield new AIChatResponseMessage(
                        $role,
                        $delta->content ?? '',
                        [$toolCall],
                    );
                }

                break;
            }

            yield new AIChatResponseMessage(
                $role,
                $delta->content ?? '',
            );
        }
    }

    /**
     * @return \Iterator<int, AIChatToolCall>
     */
    private function determineToolCall(CreateStreamedResponse $response): \Iterator
    {
        foreach ($response->choices[0]->delta->toolCalls as $toolCall) {
            yield new AIChatToolCall(
                ToolTypeEnum::from($toolCall->type),
                $toolCall->id,
                $toolCall->function->name,
                $this->decodeArguments($toolCall->function->arguments),
            );
        }
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
        return $request instanceof AIChatRequest
            && (!$request->hasTools() || Model::from($this->model)->toolsSupported());
    }
}
