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

namespace ModelflowAi\AnthropicAdapter\Chat;

use ModelflowAi\Anthropic\ClientInterface;
use ModelflowAi\Anthropic\Resources\MessagesInterface;
use ModelflowAi\Anthropic\Responses\Messages\CreateStreamedResponse;
use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\Usage;

/**
 * @phpstan-import-type Parameters from MessagesInterface
 */
final readonly class AnthropicChatAdapter implements AIChatAdapterInterface
{
    public const EXPECTED_ROLES = [
        AIChatMessageRoleEnum::SYSTEM,
        AIChatMessageRoleEnum::ASSISTANT,
        AIChatMessageRoleEnum::USER,
    ];

    public function __construct(
        private ClientInterface $client,
        private string $model,
        private int $maxTokens = 1024,
    ) {
    }

    public function handleRequest(AIChatRequest $request): AIChatResponse
    {
        /** @var Parameters $parameters */
        $parameters = [
            'model' => $this->model,
            'messages' => [],
            'max_tokens' => $this->maxTokens,
        ];

        if ($request->getOption('seed')) {
            @\trigger_error('Seed option is not supported by Anthropic.', \E_USER_WARNING);
        }

        if ($temperature = $request->getOption('temperature')) {
            /** @var float $temperature */
            $parameters['temperature'] = $temperature;
        }

        $messages = [];
        /** @var AIChatMessage $aiMessage */
        foreach ($request->getMessages() as $aiMessage) {
            if (!\in_array($aiMessage->role, self::EXPECTED_ROLES, true)) {
                throw new \Exception('Not supported message role.');
            }

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
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $part->mimeType,
                            'data' => $part->content,
                        ],
                    ];
                } else {
                    throw new \Exception('Not supported message part type.');
                }
            }

            if (1 === \count($message['content']) && 'text' === $message['content'][0]['type']) {
                $message['content'] = $message['content'][0]['text'];
            }

            $messages[] = $message;
        }

        $parameters['messages'] = $messages;

        if ('json' === $request->getFormat()) {
            $parameters['messages'][] = [
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => '{',
                    ],
                ],
            ];
        }

        if ($request instanceof AIChatStreamedRequest) {
            return $this->createStreamed($request, $parameters);
        }

        return $this->create($request, $parameters);
    }

    /**
     * @param Parameters $parameters
     */
    private function create(AIChatRequest $request, array $parameters): AIChatResponse
    {
        $result = $this->client->messages()->create($parameters);

        $content = $result->content[0]->text ?? '';
        if ('json' === $request->getFormat() && \str_ends_with($content, '}')) {
            $content = '{' . $content;
        }

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($result->role),
                $content,
            ),
            new Usage(
                $result->usage->promptTokens,
                $result->usage->completionTokens ?? 0,
                $result->usage->totalTokens,
            ),
        );
    }

    /**
     * @param Parameters $parameters
     */
    private function createStreamed(AIChatStreamedRequest $request, array $parameters): AIChatResponse
    {
        $responses = $this->client->messages()->createStreamed($parameters);

        $usageTracker = new StreamingUsageTracker(false);

        return new AIChatResponseStream(
            request: $request,
            messages: $this->createStreamedMessages($responses, 'json' === $request->getFormat() ? '{' : '', $usageTracker),
            usageTracker: $usageTracker,
        );
    }

    /**
     * @param \Iterator<int, CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    private function createStreamedMessages(\Iterator $responses, string $prefix, StreamingUsageTracker $usageTracker): \Iterator
    {
        $role = null;
        $lastUsage = null;

        foreach ($responses as $response) {
            // Anthropic sends cumulative usage with each event
            if (null !== $response->usage) {
                $lastUsage = new Usage(
                    $response->usage->promptTokens,
                    $response->usage->completionTokens ?? 0,
                    $response->usage->totalTokens,
                );
            }

            $delta = $response->content;

            if (!$role instanceof AIChatMessageRoleEnum) {
                $role = AIChatMessageRoleEnum::from($response->role ?? 'assistant');
                if ('' !== $prefix) {
                    yield new AIChatResponseMessage($role, $prefix);
                }
            }

            $text = $delta->text ?? '';
            if ('' === $text) {
                continue;
            }

            yield new AIChatResponseMessage($role, $text);
        }

        // Send final usage after stream completes
        if ($lastUsage instanceof Usage) {
            $usageTracker->updateUsage($lastUsage, true);
        }
    }

    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest
            && !$request->hasTools();
    }
}
