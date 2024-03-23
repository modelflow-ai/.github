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
use ModelflowAi\Core\Response\AIChatToolCall;
use ModelflowAi\Core\Response\AIResponseInterface;
use ModelflowAi\Core\ToolInfo\ToolTypeEnum;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Chat\CreateResponseToolCall;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\MistralAdapter\Tool\ToolFormatter;
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
     *             },
     *         },
     *     }>,
     *     tool_choice?: string,
     * } $parameters
     */
    protected function create(AIChatRequest $request, array $parameters): AIChatResponse
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
     *             },
     *         },
     *     }>,
     *     tool_choice?: string,
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

    public function supports(AIRequestInterface $request): bool
    {
        return $request instanceof AIChatRequest;
    }

    /**
     * @return \Iterator<int, AIChatToolCall>
     */
    protected function determineToolCall(CreateStreamedResponse $response): \Iterator
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
    protected function decodeArguments(string $arguments): array
    {
        /** @var array<string, mixed> $result */
        $result = \json_decode($arguments, true);

        return $result;
    }
}
