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

namespace ModelflowAi\Chat\Middleware\Tools;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\AIChatResponseStreamInterface;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\Response\UsageCallbackInterface;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;

/**
 * A decorator for AIChatResponseStream that handles tool calls in streaming responses.
 * This avoids the need to extend AIChatResponseStream while providing the same functionality.
 */
final class ToolStreamResponseDecorator implements AIChatResponseStreamInterface
{
    /** @var callable */
    private $nextMiddleware;
    private ?Usage $accumulatedUsage = null;

    /** @var list<UsageCallbackInterface> */
    private array $callbacks = [];

    public function __construct(
        private readonly AIChatResponseStreamInterface $originalStream,
        private AIChatStreamedRequest $request,
        private readonly ?AIChatAdapterInterface $adapter,
        callable $nextMiddleware,
        private readonly ToolExecutor $toolExecutor,
        private readonly int $maxToolExecutions,
        private int $executionCount = 0,
    ) {
        $this->nextMiddleware = $nextMiddleware;
    }

    public function getMessageStream(): \Iterator
    {
        $toolCalls = [];

        // First, yield all messages from the original stream
        // While doing so, collect any tool calls
        foreach ($this->originalStream->getMessageStream() as $message) {
            yield $message;

            // Collect tool calls if present
            if (null !== $message->toolCalls && \count($message->toolCalls) > 0) {
                foreach ($message->toolCalls as $toolCall) {
                    $toolCalls[] = $toolCall;
                }
            }
        }

        // After entire original stream is processed, check if we have tool calls
        if ([] !== $toolCalls && $this->executionCount < $this->maxToolExecutions) {
            ++$this->executionCount;

            // Create a new request with the assistant tool call message
            $this->request = $this->request->withMessage(
                AIChatMessage::createAssistantMessage(ToolCallsPart::create($toolCalls)),
            );

            // Execute each tool and add the tool response messages
            foreach ($toolCalls as $toolCall) {
                $toolResponseMessage = $this->toolExecutor->execute($this->request, $toolCall);
                $this->request = $this->request->withMessage($toolResponseMessage);
            }

            // Execute the request again
            $nextMiddleware = $this->nextMiddleware;

            /** @var AIChatResponseStreamInterface $nextResponse */
            $nextResponse = $nextMiddleware($this->request, $this->adapter);

            // Wrap the next response in another decorator to handle nested tool calls
            $nestedStream = new self(
                $nextResponse,
                $this->request,
                $this->adapter,
                $this->nextMiddleware,
                $this->toolExecutor,
                $this->maxToolExecutions,
                $this->executionCount,
            );

            foreach ($this->callbacks as $callback) {
                $nestedStream->registerUsageCallback($callback);
            }

            foreach ($nestedStream->getMessageStream() as $message) {
                yield $message;
            }

            $nestedUsage = $nestedStream->getUsage();
            if ($nestedUsage instanceof Usage) {
                $this->accumulatedUsage = $this->accumulatedUsage instanceof Usage
                    ? $this->accumulatedUsage->add($nestedUsage)
                    : $nestedUsage;
            }
        }
    }

    public function getRequest(): AIChatStreamedRequest
    {
        return $this->request;
    }

    public function getMessage(): AIChatResponseMessage
    {
        return $this->originalStream->getMessage();
    }

    public function getUsage(): ?Usage
    {
        // Get usage from original stream (which may be updated during streaming)
        $originalUsage = $this->originalStream->getUsage();

        // If we have accumulated usage from tool executions, add it
        if ($this->accumulatedUsage instanceof Usage) {
            return $originalUsage instanceof Usage
                ? $originalUsage->add($this->accumulatedUsage)
                : $this->accumulatedUsage;
        }

        return $originalUsage;
    }

    public function getMetadata(): array
    {
        return $this->request->getMetadata();
    }

    public function registerUsageCallback(UsageCallbackInterface $callback): void
    {
        $this->callbacks[] = $callback;
        $this->originalStream->registerUsageCallback($callback);
    }
}
