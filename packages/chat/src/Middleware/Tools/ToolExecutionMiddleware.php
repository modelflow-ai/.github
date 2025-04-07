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
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\Chat\Response\AIChatResponseStreamInterface;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;

/**
 * Middleware that automatically handles tool execution in the response,
 * supporting both regular and streamed responses with recursive tool calls.
 * Accumulates usage statistics across all tool call rounds.
 */
class ToolExecutionMiddleware implements AIChatMiddlewareInterface
{
    public function __construct(
        private readonly ToolExecutor $toolExecutor = new ToolExecutor(),
        private readonly int $maxToolExecutions = 10,
    ) {
    }

    public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface
    {
        $response = $next($request, $adapter);

        if ($response instanceof AIChatResponseStreamInterface && $request instanceof AIChatStreamedRequest) {
            return new ToolStreamResponseDecorator(
                $response,
                $request,
                $adapter,
                $next,
                $this->toolExecutor,
                $this->maxToolExecutions,
            );
        }

        return (new ToolResponseDecorator(
            $response,
            $request,
            $adapter,
            $next,
            $this->toolExecutor,
            $this->maxToolExecutions,
        ))->processToolCalls();
    }
}
