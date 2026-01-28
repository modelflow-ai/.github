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

namespace ModelflowAi\OllamaAdapter\Chat;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponse;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\Response\StreamingUsageTracker;
use ModelflowAi\Chat\Response\TokenEstimator;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Responses\Chat\CreateStreamedResponse;
use Webmozart\Assert\Assert;

final readonly class OllamaChatAdapter implements AIChatAdapterInterface
{
    public function __construct(
        private ClientInterface $client,
        private string $model = 'llama2',
    ) {
    }

    public function handleRequest(AIChatRequest $request): AIChatResponse
    {
        $format = $request->getFormat();
        Assert::inArray($format, [null, 'json', 'json_schema'], \sprintf('Invalid format "%s" given.', $format));

        $attributes = [
            'model' => $this->model,
            'messages' => $request->getMessages()->toArray(),
        ];

        if ($format) {
            $attributes['format'] = 'json';
        }

        if ($seed = $request->getOption('seed')) {
            /** @var int $seed */
            $attributes['options']['seed'] = $seed;
        }

        if ($temperature = $request->getOption('temperature')) {
            /** @var float $temperature */
            $attributes['options']['temperature'] = $temperature;
        }

        if ($request instanceof AIChatStreamedRequest) {
            return $this->createStreamed($request, $attributes);
        }

        return $this->create($request, $attributes);
    }

    /**
     * @param array{
     *     model: string,
     *     messages: array<array{
     *         role: "assistant"|"system"|"user"|"tool",
     *         content: string,
     *     }>,
     *     format?: "json",
     *     options?: array{
     *         seed?: int,
     *         temperature?: float,
     *     },
     * } $parameters
     */
    private function create(AIChatRequest $request, array $parameters): AIChatResponse
    {
        $response = $this->client->chat()->create($parameters);

        return new AIChatResponse(
            $request,
            new AIChatResponseMessage(
                AIChatMessageRoleEnum::from($response->message->role),
                $response->message->content ?? '',
            ),
            new Usage(
                $response->usage->promptTokens,
                $response->usage->completionTokens ?? 0,
                $response->usage->totalTokens,
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
     *     format?: "json",
     *     options?: array{
     *         seed?: int,
     *         temperature?: float,
     *     },
     * } $parameters
     */
    private function createStreamed(AIChatStreamedRequest $request, array $parameters): AIChatResponse
    {
        $responses = $this->client->chat()->createStreamed($parameters);

        // Ollama doesn't provide usage data in streams, so we use estimation
        $usageTracker = new StreamingUsageTracker(true);

        return new AIChatResponseStream(
            request: $request,
            messages: $this->createStreamedMessages($responses, $request, $usageTracker),
            usageTracker: $usageTracker,
        );
    }

    /**
     * @param \Iterator<int, CreateStreamedResponse> $responses
     *
     * @return \Iterator<int, AIChatResponseMessage>
     */
    private function createStreamedMessages(\Iterator $responses, AIChatRequest $request, ?StreamingUsageTracker $usageTracker = null): \Iterator
    {
        $role = null;
        $outputTokens = 0;

        foreach ($responses as $response) {
            if (!$role instanceof AIChatMessageRoleEnum) {
                $role = AIChatMessageRoleEnum::from($response->message->role);
            }

            $delta = $response->message->delta ?? '';

            if ($usageTracker instanceof StreamingUsageTracker && '' !== $delta) {
                $chunkTokens = TokenEstimator::estimateTokens($delta);
                $outputTokens += $chunkTokens;

                $usageTracker->updateUsage(
                    new Usage(0, $chunkTokens, $chunkTokens, ['estimated' => true]),
                );
            }

            yield new AIChatResponseMessage(
                $role,
                $delta,
            );
        }

        // After streaming completes, send final update with input tokens
        if ($usageTracker instanceof StreamingUsageTracker) {
            $inputTokens = 0;
            foreach ($request->getMessages() as $message) {
                foreach ($message->parts as $part) {
                    if ($part instanceof \ModelflowAi\Chat\Request\Message\TextPart) {
                        $inputTokens += TokenEstimator::estimateTokens($part->text);
                    }
                }
                $inputTokens += 4; // Message structure overhead
            }

            // Send final update with input tokens (output tokens already sent incrementally)
            $usageTracker->updateUsage(
                new Usage($inputTokens, 0, $inputTokens, ['estimated' => true]),
                true,
            );
        }
    }

    public function supports(object $request): bool
    {
        return $request instanceof AIChatRequest;
    }
}
