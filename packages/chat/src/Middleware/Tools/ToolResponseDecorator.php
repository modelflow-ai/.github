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
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\Chat\Response\AIChatResponseMessage;
use ModelflowAi\Chat\Response\Usage;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;

/**
 * A decorator for standard AIChatResponse that handles tool calls and accumulates usage statistics.
 */
final class ToolResponseDecorator implements AIChatResponseInterface
{
    /** @var callable */
    private $nextMiddleware;
    private ?Usage $usage;

    public function __construct(
        private AIChatResponseInterface $originalResponse,
        private AIChatRequest $request,
        private readonly ?AIChatAdapterInterface $adapter,
        callable $nextMiddleware,
        private readonly ToolExecutor $toolExecutor,
        private readonly int $maxToolExecutions,
        private int $executionCount = 0,
        ?Usage $usage = null,
    ) {
        $this->nextMiddleware = $nextMiddleware;
        $this->usage = $usage ?? $originalResponse->getUsage();
    }

    /**
     * Process all tool calls in the response recursively.
     */
    public function processToolCalls(): AIChatResponseInterface
    {
        $response = $this->originalResponse;
        $request = $this->request;
        $executionCount = $this->executionCount;
        $usage = $this->usage;

        while ($executionCount < $this->maxToolExecutions) {
            $toolCalls = $response->getMessage()->toolCalls ?? [];

            if (empty($toolCalls)) {
                break;
            }

            ++$executionCount;

            // Create a new request with the assistant tool call message
            $request = $request->withMessage(
                AIChatMessage::createAssistantMessage(ToolCallsPart::create($toolCalls)),
            );

            // Execute each tool and add the tool response messages
            foreach ($toolCalls as $toolCall) {
                if (!\array_key_exists($toolCall->name, $request->getTools())) {
                    continue;
                }

                $toolResponseMessage = $this->toolExecutor->execute($request, $toolCall);
                $request = $request->withMessage($toolResponseMessage);
            }

            // Execute the request again
            $nextMiddleware = $this->nextMiddleware;

            /** @var AIChatResponseInterface $nextResponse */
            $nextResponse = $nextMiddleware($request, $this->adapter);

            // Add usage statistics if available
            $usage = $usage?->add($nextResponse->getUsage());

            // Update response for next iteration
            $response = $nextResponse;
        }

        return new self(
            $response,
            $request,
            $this->adapter,
            $this->nextMiddleware,
            $this->toolExecutor,
            $this->maxToolExecutions,
            $executionCount,
            $usage,
        );
    }

    public function getMessage(): AIChatResponseMessage
    {
        return $this->originalResponse->getMessage();
    }

    public function getRequest(): AIChatRequest
    {
        return $this->request;
    }

    public function getUsage(): ?Usage
    {
        return $this->usage;
    }

    public function getMetadata(): array
    {
        return $this->request->getMetadata();
    }
}
