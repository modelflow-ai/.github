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

namespace ModelflowAi\Chat\Middleware\Adapter;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use ModelflowAi\DecisionTree\DecisionTreeInterface;

/**
 * Middleware that determines which adapter to use for a request.
 */
final readonly class AdapterDecisionMiddleware implements AIChatMiddlewareInterface
{
    /**
     * @param DecisionTreeInterface<AIChatRequest, AIChatAdapterInterface> $decisionTree
     */
    public function __construct(
        private DecisionTreeInterface $decisionTree,
    ) {
    }

    public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface
    {
        return $next($request, $this->decisionTree->determineAdapter($request));
    }
}
