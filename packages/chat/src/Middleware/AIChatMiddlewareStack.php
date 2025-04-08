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

namespace ModelflowAi\Chat\Middleware;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;

final class AIChatMiddlewareStack
{
    /**
     * @var AIChatMiddlewareInterface[]
     */
    private array $middleware = [];

    public function add(AIChatMiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function handle(AIChatRequest $request): AIChatResponseInterface
    {
        return $this->process($request, null, 0);
    }

    private function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, int $index): AIChatResponseInterface
    {
        // If we've gone through all middleware, we should have a response
        // This shouldn't happen because the AdapterExecutionMiddleware should return a response
        if ($index >= \count($this->middleware)) {
            throw new \RuntimeException('Middleware stack exhausted without producing a response. Make sure AdapterExecutionMiddleware is the last middleware in the stack.');
        }

        $middleware = $this->middleware[$index];
        $next = fn (AIChatRequest $request, ?AIChatAdapterInterface $adapter): AIChatResponseInterface => $this->process($request, $adapter, $index + 1);

        return $middleware->process($request, $adapter, $next);
    }
}
